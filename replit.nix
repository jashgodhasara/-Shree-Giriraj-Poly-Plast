{ pkgs }: {
    deps = [
        pkgs.php82
        pkgs.php82Packages.composer
        pkgs.sqlite
        pkgs.nodejs_20
    ];
}
