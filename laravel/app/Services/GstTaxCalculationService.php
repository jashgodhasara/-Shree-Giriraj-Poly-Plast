<?php

namespace App\Services;

class GstTaxCalculationService
{
    /**
     * Map of Indian GSTIN 2-digit state codes to State Names.
     */
    public const GST_STATE_CODES = [
        '01' => 'Jammu and Kashmir',
        '02' => 'Himachal Pradesh',
        '03' => 'Punjab',
        '04' => 'Chandigarh',
        '05' => 'Uttarakhand',
        '06' => 'Haryana',
        '07' => 'Delhi',
        '08' => 'Rajasthan',
        '09' => 'Uttar Pradesh',
        '10' => 'Bihar',
        '11' => 'Sikkim',
        '12' => 'Arunachal Pradesh',
        '13' => 'Nagaland',
        '14' => 'Manipur',
        '15' => 'Mizoram',
        '16' => 'Tripura',
        '17' => 'Meghalaya',
        '18' => 'Assam',
        '19' => 'West Bengal',
        '20' => 'Jharkhand',
        '21' => 'Odisha',
        '22' => 'Chhattisgarh',
        '23' => 'Madhya Pradesh',
        '24' => 'Gujarat',
        '26' => 'Dadra and Nagar Haveli and Daman and Diu',
        '27' => 'Maharashtra',
        '29' => 'Karnataka',
        '30' => 'Goa',
        '31' => 'Lakshadweep',
        '32' => 'Kerala',
        '33' => 'Tamil Nadu',
        '34' => 'Puducherry',
        '35' => 'Andaman and Nicobar Islands',
        '36' => 'Telangana',
        '37' => 'Andhra Pradesh',
        '38' => 'Ladakh',
        '97' => 'Other Territory',
    ];

    /**
     * Company's Home State (Shree Giriraj Poly Plast — Rajkot, Gujarat).
     */
    public const HOME_STATE = 'Gujarat';
    public const HOME_STATE_CODE = '24';
    public const HOME_COUNTRY = 'India';

    /**
     * Extract State Name from 15-character Indian GSTIN.
     */
    public static function getStateFromGstin(?string $gstin): ?string
    {
        if (empty($gstin)) {
            return null;
        }

        $clean = strtoupper(trim($gstin));
        if (strlen($clean) >= 2) {
            $prefix = substr($clean, 0, 2);
            return self::GST_STATE_CODES[$prefix] ?? null;
        }

        return null;
    }

    /**
     * Auto-detect Tax Regime according to Country, State, and GSTIN.
     *
     * @param string|null $country
     * @param string|null $state
     * @param string|null $gstin
     * @param string|null $taxType 'Regular', 'Export with LUT', 'SEZ', etc.
     * @return array
     */
    public static function determineTaxRegime(?string $country = 'India', ?string $state = 'Gujarat', ?string $gstin = null, ?string $taxType = 'Regular'): array
    {
        $country = trim($country ?: self::HOME_COUNTRY);
        $state   = trim($state ?: '');
        $gstin   = trim($gstin ?: '');

        // If state is not set or vague, try extracting from GSTIN
        if (empty($state) && !empty($gstin)) {
            $state = self::getStateFromGstin($gstin) ?: '';
        }

        // Default to Gujarat if completely blank in domestic
        if (empty($state) && strtolower($country) === 'india') {
            $state = self::HOME_STATE;
        }

        $isDomestic = empty($country) || in_array(strtolower($country), ['india', 'in', 'bharat', 'ind']);
        $isExport   = !$isDomestic || in_array(strtolower($taxType ?: ''), ['export', 'export with lut', 'sez', 'zero rated']);

        // 1. Export / Overseas / Zero Rated
        if ($isExport) {
            return [
                'type'             => 'EXPORT_LUT',
                'is_export'        => true,
                'is_interstate'    => false,
                'is_intrastate'    => false,
                'cgst_split'       => 0.0,
                'sgst_split'       => 0.0,
                'igst_split'       => 0.0,
                'country'          => $country,
                'state'            => $state,
                'badge_class'      => 'badge-purple',
                'label'            => 'Export / Zero-Rated (0% Tax / LUT) — ' . ($country ?: 'Overseas'),
            ];
        }

        // Check if State is Gujarat
        $isGujarat = false;
        if (!empty($gstin) && str_starts_with(strtoupper($gstin), self::HOME_STATE_CODE)) {
            $isGujarat = true;
        } elseif (strtolower($state) === 'gujarat' || strtolower($state) === 'gj') {
            $isGujarat = true;
        }

        // 2. Intra-State (Within Gujarat -> CGST + SGST)
        if ($isGujarat) {
            return [
                'type'             => 'INTRA_STATE',
                'is_export'        => false,
                'is_interstate'    => false,
                'is_intrastate'    => true,
                'cgst_split'       => 0.5, // 50% of GST rate to CGST
                'sgst_split'       => 0.5, // 50% of GST rate to SGST
                'igst_split'       => 0.0,
                'country'          => self::HOME_COUNTRY,
                'state'            => self::HOME_STATE,
                'badge_class'      => 'badge-success',
                'label'            => 'CGST + SGST (Gujarat Intra-State)',
            ];
        }

        // 3. Inter-State (Outside Gujarat -> IGST)
        return [
            'type'             => 'INTER_STATE',
            'is_export'        => false,
            'is_interstate'    => true,
            'is_intrastate'    => false,
            'cgst_split'       => 0.0,
            'sgst_split'       => 0.0,
            'igst_split'       => 1.0, // 100% of GST rate to IGST
            'country'          => self::HOME_COUNTRY,
            'state'            => $state ?: 'Inter-State',
            'badge_class'      => 'badge-indigo',
            'label'            => 'IGST (Inter-State: ' . ($state ?: 'Outside Gujarat') . ')',
        ];
    }
}
