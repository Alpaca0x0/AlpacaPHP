<div id="Navbar" class="ts-tab is-start-aligned">
    <template v-for="(item, key) in items">
        <a class="item" :class="{
            'is-active': item.is.active,
            'is-disabled': item.is.disabled,
            'u-hidden': item.is.hidden,
        }" :href="item.is.active ? '#!' : item.link">{{ item.text }}</a>
    </template>
</div>
<!-- is-active -->
<br>

<script type="module">
    import { createApp } from '<?=Vue::uri()?>';

    const Navbar = createApp({setup(){
        let items = <?=json_encode(Inc::config('navbar'))?>;
        for(let [key, item] of Object.entries(items)){
            // set attribute
            item.is = {
                active: item.is?.active ? item.is.active : false,
                disabled: item.is?.disabled ? item.is.disabled : false,
                hidden: item.is?.hidden ? item.is.hidden : false,
            };
            // active item
            let uri = '<?=htmlentities(Root.trim(Router::uri(),'/').'/')?>';
            if(uri.startsWith(item.link)){ item.is.active = true; }
        };

        return { items, };
    }}).mount('#Navbar');
</script>