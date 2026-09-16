<?php
Inc::clas('db');
// 身分階層系統：角色以 `rank` 排序，數字越大權限越高。
// `roles` 表同時也是 managers.role 的 FK 對象（見 classes/manager.php），root 也是其中一筆（rank 最高）。
// 資料表結構請參考 /database/schema.sql
class Permission{
    // 取得所有已定義的權限（僅作為範例保留，未與角色綁定）
    static function getAll(){
        if(!DB::connect()){ return false; }
        DB::query("SELECT `id`, `name`, `text` FROM `permissions` ORDER BY `id`;")::execute();
        $ret = DB::fetchAll();
        return DB::error() ? false : $ret;
    }

    // 新增權限
    static function add($name, $text){
        if(!DB::connect()){ return false; }
        DB::query("INSERT INTO `permissions` (`name`, `text`) VALUES (:name, :text);")::execute([
            ':name' => $name,
            ':text' => $text,
        ]);
        if(DB::error()){ return false; }
        return Type::int(DB::lastInsertId());
    }

    // 編輯權限（`name` 為識別碼，固定不可修改）
    static function edit($id, $text){
        if(!DB::connect()){ return false; }
        DB::query("UPDATE `permissions` SET `text` = :text WHERE `id` = :id;")::execute([
            ':text' => $text,
            ':id' => $id,
        ]);
        return !DB::error();
    }

    // 取得所有已定義的身分組，依 rank 由高到低排序
    static function getAllRoles(){
        if(!DB::connect()){ return false; }
        DB::query("SELECT `id`, `name`, `text`, `rank` FROM `roles` ORDER BY `rank` DESC, `id`;")::execute();
        $ret = DB::fetchAll();
        return DB::error() ? false : $ret;
    }

    // 取得單一身分組
    static function getRole($id){
        if(!DB::connect()){ return false; }
        DB::query("SELECT `id`, `name`, `text`, `rank` FROM `roles` WHERE `id` = :id LIMIT 1;")::execute([':id' => $id]);
        $data = DB::fetch();
        return DB::error() || !$data ? false : $data;
    }

    // 依 name（角色 key，例如 'dev'）取得單一身分組；給 classes/manager.php 把 role key 轉成 roles.id 用
    static function getRoleByName($name){
        if(!DB::connect()){ return false; }
        DB::query("SELECT `id`, `name`, `text`, `rank` FROM `roles` WHERE `name` = :name LIMIT 1;")::execute([':name' => $name]);
        $data = DB::fetch();
        return DB::error() || !$data ? false : $data;
    }

    // 新增身分組
    static function addRole($name, $text, $rank=0){
        if(!DB::connect()){ return false; }
        DB::query("INSERT INTO `roles` (`name`, `text`, `rank`) VALUES (:name, :text, :rank);")::execute([
            ':name' => $name,
            ':text' => $text,
            ':rank' => $rank,
        ]);
        if(DB::error()){ return false; }
        return Type::int(DB::lastInsertId());
    }

    // 編輯身分組（`name` 為識別碼，固定不可修改）
    static function editRole($id, $text, $rank){
        if(!DB::connect()){ return false; }
        DB::query("UPDATE `roles` SET `text` = :text, `rank` = :rank WHERE `id` = :id;")::execute([
            ':text' => $text,
            ':rank' => $rank,
            ':id' => $id,
        ]);
        return !DB::error();
    }
}
