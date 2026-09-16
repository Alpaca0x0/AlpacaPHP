<?php
Inc::clas('db');
Inc::clas('permission');
// Session 制登入系統。
// - `role` 關聯 `roles`.`id`（見 classes/permission.php、database/schema.sql），NOT NULL，預設 common；
//   root 也是 `roles` 表裡的一筆（rank 最高），須明確寫上，不再用 NULL 表示；任何人（含其他 root）都無法
//   控制 root，見 self::canControl()。Manager 對外一律用 role 的 key（`roles`.`name`，例如 'dev'），
//   id ↔ key 的轉換透過 Permission::getRoleByName() 查表。
// - Manager::current() 只讀 session（key 'manager'：id/account/name/token），不碰資料庫，
//   給前端/僅用於顯示的場景使用。
// - 後端要驗證權限時不能只靠 session，必須查資料庫確認 token 沒過期，因此把 Manager 設計成可實體化：
//     new Manager()                 // 從 session 取得目前登入帳號，並以資料庫驗證 token
//     new Manager(id: 2)            // 依 id 查詢帳號（不驗證 token，僅單純取得該帳號資料）
//     new Manager(account: 'abc')   // 依 account 查詢帳號（不驗證 token）
//   查無帳號、或（session 情境下）token 已過期時，$this->id 等屬性維持 null。
// session 由入口路由 (router.php) 統一啟動，見該檔案。
class Manager{
    public $id, $account, $name, $role, $password, $token;

    function __construct($id=null, $account=null){
        if(!DB::connect()){ return; }

        // 未指定 id/account：從 session 取得目前登入帳號，並以資料庫驗證 token 是否過期
        if($id === null && $account === null){
            $session = self::current();
            $token = Type::string($session['token'] ?? '', '');
            if($token === ''){ return; }

            DB::query('SELECT `m`.`id`, `m`.`account`, `m`.`name`, `r`.`name` AS `role`, `m`.`password`
                FROM `manager_events` `me`
                JOIN `managers` `m` ON `m`.`id` = `me`.`manager`
                JOIN `roles` `r` ON `r`.`id` = `m`.`role`
                WHERE `me`.`token` = :token AND `me`.`commit` = :commit AND `me`.`expire` > NOW()
                ORDER BY `me`.`id` DESC
                LIMIT 1
            ;')::execute([':token' => $token, ':commit' => 'login']);
            $manager = DB::fetch();
            if(DB::error() || !$manager){ unset($_SESSION['manager']); return; } // token 已過期或不存在，session 一併失效

            $this->id = $manager['id'];
            $this->account = $manager['account'];
            $this->name = $manager['name'];
            $this->role = $manager['role'];
            $this->password = $manager['password'];
            $this->token = $token;

            // 每次通過驗證的請求都延長 token 存活時間
            $config = Inc::config('manager');
            DB::query('UPDATE `manager_events` SET `expire` = DATE_ADD(NOW(), INTERVAL :seconds SECOND)
                WHERE `token` = :token AND `commit` = :commit;
            ')::execute([':seconds' => $config['login']['timeout'], ':token' => $token, ':commit' => 'login']);
            return;
        }

        // 指定 id 或 account：單純查詢該帳號資料，不做 token 驗證
        if($id !== null){
            $where = '`m`.`id` = :value';
            $value = Type::int($id);
        }else{
            $where = '`m`.`account` = :value';
            $value = Type::string($account);
        }
        DB::query("SELECT `m`.`id`, `m`.`account`, `m`.`name`, `r`.`name` AS `role`, `m`.`password`
            FROM `managers` `m`
            JOIN `roles` `r` ON `r`.`id` = `m`.`role`
            WHERE {$where}
            LIMIT 1
        ;")::execute([':value' => $value]);
        $manager = DB::fetch();
        if(DB::error() || !$manager){ return; }

        $this->id = $manager['id'];
        $this->account = $manager['account'];
        $this->name = $manager['name'];
        $this->role = $manager['role'];
        $this->password = $manager['password'];
    }

    // 只讀 session，不碰資料庫；未登入回傳 null
    static function current(){
        return $_SESSION['manager'] ?? null;
    }

    // 把目前登入 token 在資料庫中設為過期，並清除 session
    static function logout(){
        $session = self::current();
        if(!$session){ return true; }
        if(DB::connect()){
            DB::query('UPDATE `manager_events` SET `expire` = NOW()
                WHERE `token` = :token AND `commit` = :commit AND `expire` > NOW();
            ')::execute([':token' => Type::string($session['token'] ?? '', ''), ':commit' => 'login']);
        }
        unset($_SESSION['manager']);
        return true;
    }

    // 新增帳號；$role 為 roles.name（例如 'dev'）；不指定時給 null，交給資料庫欄位預設值（common）
    static function add($account, $password, $name, $role=null){
        if(!DB::connect()){ return false; }
        $roleId = null;
        if($role !== null){
            $roleData = Permission::getRoleByName($role);
            if(!$roleData){ return false; }
            $roleId = $roleData['id'];
        }
        if($roleId === null){
            DB::query('INSERT INTO `managers` (`account`, `password`, `name`) VALUES (:account, :password, :name);')::execute([
                ':account' => $account,
                ':password' => password_hash($password, PASSWORD_ARGON2ID),
                ':name' => $name,
            ]);
        }else{
            DB::query('INSERT INTO `managers` (`account`, `password`, `name`, `role`) VALUES (:account, :password, :name, :role);')::execute([
                ':account' => $account,
                ':password' => password_hash($password, PASSWORD_ARGON2ID),
                ':name' => $name,
                ':role' => $roleId,
            ]);
        }
        if(DB::error()){ return false; }
        return Type::int(DB::lastInsertId());
    }

    // 更新指定帳號的身分組；$role 為 roles.name（例如 'dev'）
    static function setRole($manager, $role){
        if(!DB::connect()){ return false; }
        $roleData = Permission::getRoleByName($role);
        if(!$roleData){ return false; }
        DB::query('UPDATE `managers` SET `role` = :role WHERE `id` = :id;')::execute([
            ':role' => $roleData['id'],
            ':id' => Type::int($manager),
        ]);
        return !DB::error();
    }

    // 判斷擁有 $actorRole 身分的人，是否可以控制擁有 $targetRole 身分的人：
    // 依 roles.rank 比較，只能控制 rank 比自己低的身分組，同層（含 root 對 root）也不行。
    static function canControl($actorRole, $targetRole){
        $actor = Permission::getRoleByName($actorRole);
        $target = Permission::getRoleByName($targetRole);
        if(!$actor || !$target){ return false; }
        return $actor['rank'] > $target['rank'];
    }
}
