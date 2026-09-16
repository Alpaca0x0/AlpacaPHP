<?php
Inc::clas('db');
Inc::clas('permission');
// 最小可用的登入系統。身分階層規則：
// - `role` 為 NULL 表示 root，可以控制所有人（包含其他 root）。
// - 非 root 只能控制 rank 比自己低的身分組；root 無法被任何非 root 控制。
// session 由入口路由 (router.php) 統一啟動，見該檔案。
class Manager{
    // 帳號登入，成功時將 manager id 寫入 session
    static function login($username, $password){
        if(!DB::connect()){ return false; }
        DB::query("SELECT `id`, `password` FROM `managers` WHERE `username` = :username LIMIT 1;")::execute([':username' => $username]);
        $data = DB::fetch();
        if(DB::error() || !$data || !password_verify($password, $data['password'])){ return false; }
        $_SESSION['manager'] = $data['id'];
        return true;
    }

    static function logout(){
        unset($_SESSION['manager']);
        return true;
    }

    // 取得目前登入的帳號資料，未登入回傳 false
    static function current(){
        if(empty($_SESSION['manager'])){ return false; }
        return self::get($_SESSION['manager']);
    }

    static function isLoggedIn(){ return self::current() !== false; }

    // 新增帳號；$roleId 為 null 表示 root
    static function add($username, $password, $roleId){
        if(!DB::connect()){ return false; }
        DB::query("INSERT INTO `managers` (`username`, `password`, `role`) VALUES (:username, :password, :role);")::execute([
            ':username' => $username,
            ':password' => password_hash($password, PASSWORD_ARGON2ID),
            ':role' => $roleId,
        ]);
        if(DB::error()){ return false; }
        return Type::int(DB::lastInsertId());
    }

    // `role` = NULL 代表 root；`roleText`/`rank` 皆為 NULL 時也代表 root
    static function get($id){
        if(!DB::connect()){ return false; }
        DB::query("SELECT `m`.`id`, `m`.`username`, `m`.`role`, `r`.`text` AS `roleText`, `r`.`rank`
            FROM `managers` `m`
            LEFT JOIN `roles` `r` ON `r`.`id` = `m`.`role`
            WHERE `m`.`id` = :id
            LIMIT 1
        ;")::execute([':id' => $id]);
        $data = DB::fetch();
        if(DB::error() || !$data){ return false; }
        $data['isRoot'] = $data['role'] === null;
        return $data;
    }

    static function getAll(){
        if(!DB::connect()){ return false; }
        DB::query("SELECT `m`.`id`, `m`.`username`, `m`.`role`, `r`.`text` AS `roleText`, `r`.`rank`
            FROM `managers` `m`
            LEFT JOIN `roles` `r` ON `r`.`id` = `m`.`role`
            ORDER BY `m`.`id`
        ;")::execute();
        $ret = DB::fetchAll();
        if(DB::error()){ return false; }
        foreach($ret as &$row){ $row['isRoot'] = $row['role'] === null; }
        unset($row);
        return $ret;
    }

    // 更新指定帳號的身分組；$roleId 為 null 表示設為 root
    static function setRole($id, $roleId){
        if(!DB::connect()){ return false; }
        DB::query("UPDATE `managers` SET `role` = :role WHERE `id` = :id;")::execute([
            ':role' => $roleId,
            ':id' => $id,
        ]);
        return !DB::error();
    }

    // 判斷擁有 $actorRole 身分的人，是否可以控制擁有 $targetRole 身分的人
    // $actorRole / $targetRole 皆為 managers.role 的值：null 代表 root
    static function canControl($actorRole, $targetRole){
        if($targetRole === null){ return false; } // root 之間互為同層，沒有人（包含其他 root）可以控制 root
        if($actorRole === null){ return true; } // root 可以控制所有非 root 的人
        $actor = Permission::getRole($actorRole);
        $target = Permission::getRole($targetRole);
        if(!$actor || !$target){ return false; }
        return $actor['rank'] > $target['rank']; // 只能控制層級比自己低的人，同層也不行
    }
}
