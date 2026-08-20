// Global polyfill to protect Expo Modules from [runtime not ready] / missing JSI EventEmitter errors
class PolyfillEventEmitter {
  private listeners = new Map<string, Set<Function>>();

  addListener(eventName: string, listener: Function) {
    if (!this.listeners.has(eventName)) {
      this.listeners.set(eventName, new Set());
    }
    this.listeners.get(eventName)!.add(listener);
    return {
      remove: () => {
        this.listeners.get(eventName)?.delete(listener);
      },
    };
  }

  removeListener(eventName: string, listener: Function) {
    this.listeners.get(eventName)?.delete(listener);
  }

  removeAllListeners(eventName?: string) {
    if (eventName) {
      this.listeners.get(eventName)?.clear();
    } else {
      this.listeners.clear();
    }
  }

  emit(eventName: string, ...args: any[]) {
    this.listeners.get(eventName)?.forEach((listener) => {
      try {
        listener(...args);
      } catch (e) {
        console.error(e);
      }
    });
  }

  listenerCount(eventName: string) {
    return this.listeners.get(eventName)?.size ?? 0;
  }
}

class PolyfillNativeModule extends PolyfillEventEmitter {}
class PolyfillSharedObject extends PolyfillEventEmitter {
  release() {}
}
class PolyfillSharedRef extends PolyfillSharedObject {
  nativeRefType = 'unknown';
}

const g = typeof globalThis !== 'undefined' ? (globalThis as any) : (global as any);

if (g) {
  if (!g.expo) {
    g.expo = {};
  }
  if (!g.expo.EventEmitter) {
    g.expo.EventEmitter = PolyfillEventEmitter;
  }
  if (!g.expo.NativeModule) {
    g.expo.NativeModule = PolyfillNativeModule;
  }
  if (!g.expo.SharedObject) {
    g.expo.SharedObject = PolyfillSharedObject;
  }
  if (!g.expo.SharedRef) {
    g.expo.SharedRef = PolyfillSharedRef;
  }
  if (!g.expo.modules) {
    g.expo.modules = {};
  }
}

export {};
