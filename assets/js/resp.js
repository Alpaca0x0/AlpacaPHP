export default {
    object(obj){
        return ['type','status','data','message'].every((key) => { return Object.keys(obj).includes(key); }) ? obj : false;
    }
}