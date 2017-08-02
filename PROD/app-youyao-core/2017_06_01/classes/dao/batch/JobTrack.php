<?php
apf_require_class("APF_DB_Factory");

class Dao_Batch_JobTrack {

  private $pdo;
  private $slave_pdo;
  private $one_pdo;
  private $one_slave_pdo;

  public function __construct() {
    $this->pdo = APF_DB_Factory::get_instance()->get_pdo("lkymaster");
    $this->slave_pdo = APF_DB_Factory::get_instance()->get_pdo("lkyslave");
    $this->one_pdo = APF_DB_Factory::get_instance()->get_pdo("master");
    $this->one_slave_pdo = APF_DB_Factory::get_instance()->get_pdo("slave");
  }

  public function getDataJobLastProcessTime($jobName, $defaut = '2012-01-01')
  {
    $lastProcessTime = strtotime($defaut);

    $sql = "SELECT * from t_data_job_tracking where job_name = ? and status = 1 order by last_process_date desc";
    $stmt = $this->pdo->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
    $stmt->execute(array($jobName));
    if (($row = $stmt->fetch(\PDO::FETCH_ASSOC)) && !empty($row['last_process_date'])) {
      $lastProcessTime = strtotime($row['last_process_date']);
    }
    $stmt->closeCursor();

    return $lastProcessTime;
  }

  public function getLastMiliseconds($jobName, $defaut = '2012-01-01')
  {
    $lastMilisecond = strtotime($defaut)*1000;

    $sql = "SELECT * from t_data_job_tracking where job_name = ? and status = 1 order by last_milisecond desc, last_process_date desc";
    $stmt = $this->pdo->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
    $stmt->execute(array($jobName));
    if (($row = $stmt->fetch(\PDO::FETCH_ASSOC)) && (!empty($row['last_process_date']) || !empty($row['last_milisecond']))) {
      if (empty($row['last_milisecond'])) {
        $lastMilisecond = strtotime($row['last_process_date'])*1000;
      } else {
        $lastMilisecond = $row['last_milisecond'];
      }
    }
    $stmt->closeCursor();

    return $lastMilisecond;
  }

  public function createDataJobTrack($jobName, $comments)
  {
    $sql = "INSERT INTO t_data_job_tracking SET job_name = ?, create_date = NOW(), comments = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(array(
        $jobName,
        $comments
    ));
    $jobId = (int)$this->pdo->lastInsertId();
    return $jobId;
  }

  public function updateDataJobTrack($jobId, $duration, $status, $lastProcessDate, $comments, $lastMilisecond = 0)
  {
    $sql = "UPDATE t_data_job_tracking SET duration = ?, status = ?, last_process_date = ?, last_milisecond = ?, comments = ? WHERE id = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(array(
        $duration,
        $status,
        $lastProcessDate,
        $lastMilisecond,
        $comments,
        $jobId
    ));
  }

}
