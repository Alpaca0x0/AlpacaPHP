import Cookie from './cookie.js';

const TARGET_ATTR_SUFFIXES = {
    '-placeholder': 'placeholder',
    '-title': 'title',
    '-alt': 'alt',
    '-value': 'value',
    '-arialabel': 'aria-label',
};

class Lang {
    static set(code, reload=true) {
        code = String(code ?? '').trim();
        if(!code) return;
        Cookie.set('lang', code, 60 * 60 * 24 * 365, '/');
        if(reload){
            window.location.reload();
        }
    }

    constructor() {
        this.data = {};
        this.code = Cookie.get('lang') ? String(Cookie.get('lang')).trim() : null;
        this.Directive = {
            mounted: (el, binding) => { this.applyTranslationIfExists(el, binding); },
            updated: (el, binding) => { this.applyTranslationIfExists(el, binding); },
        };
        this.t = this.t.bind(this);
    }

    set(code, reload=true) { Lang.set(code, reload); }

    load(data={}) { this.data = data; }

    get(key, defaultValue=false, vars={}) {
        let value = this.data;
        let found = true;

        for (const segment of String(key ?? '').split('.')) {
            if (!value || typeof value !== 'object' || !Object.prototype.hasOwnProperty.call(value, segment)) {
                found = false;
                break;
            }
            value = value[segment];
        }

        value = found ? (value ?? defaultValue) : defaultValue;

        if (typeof value === 'string' && vars && typeof vars === 'object') {
            value = value.replace(/\{\{([a-zA-Z0-9_]+)\}\}/g, (match, token) => {
                if (!Object.prototype.hasOwnProperty.call(vars, token)) {
                    return match;
                }
                const replacement = vars[token];
                const isScalar = ['string', 'number', 'boolean', 'bigint'].includes(typeof replacement);
                return isScalar ? String(replacement) : match;
            });
        }

        return value;
    }

    t(key, fallback='', vars={}) {
        const translated = this.getTranslatedTextIfExists(key, vars);
        if(translated !== null) return translated;
        return typeof fallback === 'string' ? fallback : String(fallback ?? '');
    }

    getTranslatedTextIfExists(key, vars={}) {
        if(typeof key !== 'string' || !key.trim()) return null;
        const normalizedKey = key.trim();

        const translated = this.get(normalizedKey, null, vars);
        if(typeof translated === 'string' && translated.trim() && translated !== normalizedKey){
            return translated;
        }

        const ciValue = this._getValueByPathCaseInsensitive(this.data, normalizedKey);
        if(typeof ciValue !== 'string') return null;

        const ciTranslated = this._interpolateVars(ciValue, vars);
        if(!ciTranslated.trim()) return null;

        return ciTranslated;
    }

    applyTranslationIfExists(el, binding) {
        if(!el) return;

        const { key, vars, targetAttr } = this._resolveBinding(binding);
        const translated = this.getTranslatedTextIfExists(key, vars);
        if(translated === null) return;

        if(targetAttr){
            el.setAttribute(targetAttr, translated);
            if(targetAttr === 'value' && 'value' in el){
                el.value = translated;
            }
            return;
        }

        el.textContent = translated;
    }

    applyByLangAttribute(root=document) {
        if(!root || typeof root.querySelectorAll !== 'function') return;

        const elements = root.querySelectorAll('[lang]');
        elements.forEach((el) => {
            const key = String(el.getAttribute('lang') ?? '').trim();
            if(!key) return;

            const translated = this.getTranslatedTextIfExists(key);
            if(translated === null) return;

            el.textContent = translated;
        });
    }

    _interpolateVars(value, vars={}) {
        if(typeof value !== 'string') return value;
        if(!vars || typeof vars !== 'object') return value;

        return value.replace(/\{\{([a-zA-Z0-9_]+)\}\}/g, (match, token) => {
            if(!Object.prototype.hasOwnProperty.call(vars, token)){
                return match;
            }
            const replacement = vars[token];
            const isScalar = ['string', 'number', 'boolean', 'bigint'].includes(typeof replacement);
            return isScalar ? String(replacement) : match;
        });
    }

    _getValueByPathCaseInsensitive(source, key) {
        let current = source;

        for(const segment of String(key ?? '').split('.')){
            if(!current || typeof current !== 'object') return null;

            if(Object.prototype.hasOwnProperty.call(current, segment)){
                current = current[segment];
                continue;
            }

            const lowerSegment = segment.toLowerCase();
            const matchedKey = Object.keys(current).find((k) => String(k).toLowerCase() === lowerSegment);
            if(!matchedKey) return null;

            current = current[matchedKey];
        }

        return current;
    }

    _resolveBinding(binding) {
        if(typeof binding?.value === 'string'){
            return { key: binding.value, vars: {}, targetAttr: null };
        }

        if(binding?.value && typeof binding.value === 'object'){
            return {
                key: binding.value.key ?? null,
                vars: binding.value.vars ?? {},
                targetAttr: typeof binding.value.targetAttr === 'string' ? binding.value.targetAttr : null,
            };
        }

        if(typeof binding?.arg === 'string' && binding.arg.trim()){
            const keyParts = [binding.arg.trim(), ...Object.keys(binding.modifiers ?? {})].filter(Boolean);
            let targetAttrFromSuffix = null;

            if(keyParts.length > 0){
                let lastPart = keyParts[keyParts.length - 1];
                for(const [suffix, attrName] of Object.entries(TARGET_ATTR_SUFFIXES)){
                    if(!lastPart.endsWith(suffix)) continue;

                    lastPart = lastPart.slice(0, -suffix.length);
                    targetAttrFromSuffix = attrName;
                    break;
                }

                keyParts[keyParts.length - 1] = lastPart;
            }

            return {
                key: keyParts.join('.'),
                vars: {},
                targetAttr: targetAttrFromSuffix,
            };
        }

        return { key: null, vars: {}, targetAttr: null };
    }
}

export default Lang;
export { Lang };
