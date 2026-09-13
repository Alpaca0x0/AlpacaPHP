<?php
Inc::clas('manager');
$me = Manager::current();
if($me === false){ Router::redirect('/login/'); }
?>
<?php Inc::component('header'); ?>
<?php Inc::component('navbar'); ?>

<div id="Demo" class="ts-container">
    <p>
        目前登入：<strong>{{ me.username }}</strong>
        （<strong>{{ me.isRoot ? 'Root' : me.roleText }}</strong>）
        　<a href="<?=Uri::page('logout/')?>">登出</a>
    </p>

    <div class="ts-divider is-section"></div>

    <h3>新增帳號</h3>
    <form @submit.prevent="addManager()">
        <div class="ts-grid is-3-columns">
            <div class="column">
                <div class="ts-text is-label">帳號</div>
                <div class="ts-input u-top-spaced"><input type="text" v-model="newManager.username"></div>
            </div>
            <div class="column">
                <div class="ts-text is-label">密碼</div>
                <div class="ts-input u-top-spaced"><input type="password" v-model="newManager.password"></div>
            </div>
            <div class="column">
                <div class="ts-text is-label">身分組</div>
                <div class="ts-select u-top-spaced">
                    <select v-model="newManager.role">
                        <option v-for="role in assignableRoles" :key="role.id" :value="role.id">{{ role.text }}</option>
                    </select>
                </div>
            </div>
        </div>
        <button class="ts-button u-top-spaced" type="submit" :disabled="is.adding">新增</button>
        <span class="u-left-spaced" v-if="addMessage">{{ addMessage }}</span>
    </form>

    <div class="ts-divider is-section"></div>

    <h3>帳號清單</h3>
    <table class="ts-table is-fitted">
        <thead>
            <tr><th>帳號</th><th>身分組</th><th>調整身分組</th></tr>
        </thead>
        <tbody>
            <tr v-for="m in managers" :key="m.id">
                <td>{{ m.username }}</td>
                <td>{{ m.isRoot ? 'Root' : m.roleText }}</td>
                <td>
                    <template v-if="m.canControl">
                        <select v-model="m._newRole">
                            <option v-if="me.isRoot" :value="null">Root</option>
                            <option v-for="role in controllableRoles" :key="role.id" :value="role.id">{{ role.text }}</option>
                        </select>
                        <button class="ts-button is-small" type="button" @click="setRole(m)" :disabled="is.saving === m.id">儲存</button>
                    </template>
                    <template v-else>-</template>
                </td>
            </tr>
        </tbody>
    </table>
    <p v-if="managerMessage">{{ managerMessage }}</p>
</div>

<div class="ts-divider is-section"></div>

<div class="ts-container">
    <?php Inc::component('router'); ?>
</div>

<script type="module">
    import '<?=Uri::js('ajax')?>';
    import { createApp, reactive, ref, computed } from '<?=Uri::js('vue')?>';

    createApp({setup(){
        let me = reactive(<?=json_encode($me, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)?>);
        let roles = reactive([]);
        let managers = reactive([]);
        let newManager = reactive({ username: '', password: '', role: null });
        let is = reactive({ adding: false, saving: false });
        let addMessage = ref('');
        let managerMessage = ref('');

        // 我自己的 rank；root 視為無限大
        const myRank = computed(() => {
            if(me.isRoot){ return Infinity; }
            const r = roles.find(r => r.id === me.role);
            return r ? r.rank : -Infinity;
        });

        // 我可以指派（新增帳號、調整他人）的身分組：root 可指派全部，其餘只能指派比自己低的
        const assignableRoles = computed(() => me.isRoot ? roles : roles.filter(r => r.rank < myRank.value));
        const controllableRoles = assignableRoles;

        const loadRoles = () => {
            $.ajax({ url: '<?=Uri::api('permission/roles/get')?>/' }).done((resp) => {
                if(resp.type !== 'success'){ return; }
                roles.splice(0, roles.length, ...resp.data);
                if(newManager.role === null && assignableRoles.value.length){
                    newManager.role = assignableRoles.value[0].id;
                }
            });
        };

        const loadManagers = () => {
            $.ajax({ url: '<?=Uri::api('manager/get')?>/' }).done((resp) => {
                if(resp.type !== 'success'){ managerMessage.value = resp.message; return; }
                const list = resp.data.managers.map(m => ({ ...m, _newRole: m.role }));
                managers.splice(0, managers.length, ...list);
            });
        };

        const addManager = () => {
            is.adding = true;
            addMessage.value = '';
            $.ajax({
                type: 'post',
                url: '<?=Uri::api('manager/add')?>/',
                data: newManager,
            }).done((resp) => {
                addMessage.value = resp.message;
                if(resp.type === 'success'){
                    newManager.username = '';
                    newManager.password = '';
                    loadManagers();
                }
            }).fail(() => { addMessage.value = '新增時發生錯誤'; })
            .always(() => { is.adding = false; });
        };

        const setRole = (m) => {
            is.saving = m.id;
            managerMessage.value = '';
            $.ajax({
                type: 'post',
                url: '<?=Uri::api('manager/role/set')?>/',
                data: { id: m.id, role: m._newRole === null ? '' : m._newRole },
            }).done((resp) => {
                managerMessage.value = resp.message;
                if(resp.type === 'success'){ loadManagers(); }
            }).fail(() => { managerMessage.value = '更新時發生錯誤'; })
            .always(() => { is.saving = false; });
        };

        loadRoles();
        loadManagers();

        return {
            me, roles, managers, newManager, is, addMessage, managerMessage,
            assignableRoles, controllableRoles, addManager, setRole,
        };
    }}).mount('#Demo');
</script>

<?php Inc::component('footer'); ?>
