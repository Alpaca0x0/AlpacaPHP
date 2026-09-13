const LocalStorage = {
    set(name, value) {
        window.localStorage.setItem(name, String(value));
    },
    get(name) {
        return window.localStorage.getItem(name);
    },
    load(name) {
        try {
            const obj = JSON.parse(LocalStorage.get(name));
            return typeof obj === 'object' && obj !== null ? obj : {};
        } catch (error) {
            return {};
        }
    },
};

export default LocalStorage;
export { LocalStorage };
