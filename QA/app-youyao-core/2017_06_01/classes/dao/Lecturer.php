<?php

class Dao_Lecturer
{

    public function __construct()
    {
        $this->lkyslavePdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    }

    public function all()
    {
        $sql = 'select * from LKYou.t_teacher_share where active=1';
        $stmt = $this->lkyslavePdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
