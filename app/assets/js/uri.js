const Uri = {
    parse(search = window.location.search) {
        return Object.fromEntries(new URLSearchParams(search));
    },
    get(name, search = window.location.search) {
        return new URLSearchParams(search).get(name);
    },
    has(name, search = window.location.search) {
        return new URLSearchParams(search).has(name);
    },
    set(name, value, push = false) {
        const url = new URL(window.location.href);
        url.searchParams.set(name, value);
        push ? window.history.pushState({}, '', url) : window.history.replaceState({}, '', url);
    },
    remove(name, push = false) {
        const url = new URL(window.location.href);
        url.searchParams.delete(name);
        push ? window.history.pushState({}, '', url) : window.history.replaceState({}, '', url);
    },
    build(path, params = {}) {
        const url = new URL(path, window.location.origin);
        for(const [key, value] of Object.entries(params)){
            if(value !== undefined && value !== null) url.searchParams.set(key, value);
        }
        return url.toString();
    },
};

export default Uri;
export { Uri };
