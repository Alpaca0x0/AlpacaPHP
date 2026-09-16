<?php
Inc::clas('manager');
// 這個頁面只有登入才能開啟（見 routers/dashboard.php），故此處一定拿得到目前登入帳號的資料。
// role 不放在 session 裡，畫面需要 role（判斷可指派的身分組）一律以資料庫驗證過的 new Manager() 取得。
$me = new Manager();
// 只把顯示需要的欄位傳給前端，password（雜湊）/token 不外流
$meData = ['id' => $me->id, 'account' => $me->account, 'name' => $me->name, 'role' => $me->role];
?>
<?php Inc::component('header'); ?>
<?php Inc::component('navbar'); ?>

<div id="Demo" class="ts-container">
    <p>
        目前登入：<strong>{{ me.name }}</strong>（{{ me.account }}，<strong>{{ roleLabel[me.role] }}</strong>）
        　<a href="<?=Uri::page('dashboard/manager/logout/')?>">登出</a>
    </p>

    <div class="ts-divider is-section"></div>

    <h3>新增帳號</h3>
    <form @submit.prevent="addManager()">
        <div class="ts-grid is-4-columns">
            <div class="column">
                <div class="ts-text is-label">帳號</div>
                <div class="ts-input u-top-spaced"><input type="text" v-model="newManager.account"></div>
            </div>
            <div class="column">
                <div class="ts-text is-label">密碼</div>
                <div class="ts-input u-top-spaced"><input type="password" v-model="newManager.password"></div>
            </div>
            <div class="column">
                <div class="ts-text is-label">名稱</div>
                <div class="ts-input u-top-spaced"><input type="text" v-model="newManager.name"></div>
            </div>
            <div class="column">
                <div class="ts-text is-label">身分組</div>
                <div class="ts-select u-top-spaced">
                    <select v-model="newManager.role">
                        <option v-for="role in assignableRoles" :key="role" :value="role">{{ roleLabel[role] }}</option>
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
            <tr><th>帳號</th><th>名稱</th><th>身分組</th><th>調整身分組</th></tr>
        </thead>
        <tbody>
            <tr v-for="m in managers" :key="m.id">
                <td>{{ m.account }}</td>
                <td>{{ m.name }}</td>
                <td>{{ roleLabel[m.role] }}</td>
                <td>
                    <template v-if="m.canControl">
                        <select v-model="m._newRole">
                            <option v-for="role in assignableRoles" :key="role" :value="role">{{ roleLabel[role] }}</option>
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
        let me = reactive(<?=json_encode($meData, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)?>);
        let roles = reactive([]); // [{id, name, text, rank}]，來自 api/dashboard/permission/roles/get
        let managers = reactive([]);
        let newManager = reactive({ account: '', password: '', name: '', role: null });
        let is = reactive({ adding: false, saving: false });
        let addMessage = ref('');
        let managerMessage = ref('');

        const roleLabel = computed(() => Object.fromEntries(roles.map(r => [r.name, r.text])));
        // 我自己的 rank
        const myRank = computed(() => (roles.find(r => r.name === me.role) ?? {}).rank ?? -Infinity);
        // 我可以指派（新增帳號、調整他人）的身分組：只能指派 rank 比自己低的，同層（含 root 對 root）也不行
        const assignableRoles = computed(() => roles.filter(r => r.rank < myRank.value).map(r => r.name));

        const loadRoles = () => {
            $.ajax({ url: '<?=Uri::api('dashboard/permission/roles/get')?>/' }).done((resp) => {
                if(resp.type !== 'success'){ return; }
                roles.splice(0, roles.length, ...resp.data);
                if(newManager.role === null && assignableRoles.value.length){
                    newManager.role = assignableRoles.value[0];
                }
            });
        };

        const loadManagers = () => {
            $.ajax({ url: '<?=Uri::api('dashboard/manager/get')?>/' }).done((resp) => {
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
                url: '<?=Uri::api('dashboard/manager/add')?>/',
                data: newManager,
            }).done((resp) => {
                addMessage.value = resp.message;
                if(resp.type === 'success'){
                    newManager.account = '';
                    newManager.password = '';
                    newManager.name = '';
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
                url: '<?=Uri::api('dashboard/manager/role/set')?>/',
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
            me, roleLabel, managers, newManager, is, addMessage, managerMessage,
            assignableRoles, addManager, setRole,
        };
    }}).mount('#Demo');
</script>

<?php Inc::component('footer'); ?>
