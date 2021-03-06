<?php
/**
 * PDO handler to save profiler data
 */
class Xhgui_Saver_Pdo implements \Xhgui_Saver_Interface {

    /**
     * @var \PDO
     */
    protected $connection;

    /**
     * PDO constructor.
     *
     * @param string $dsn
     * @param string $userName
     * @param string $password
     * @param array  $options
     */
    public function __construct($dsn, $userName, $password, $options = array())
    {
        $this->connection = new \PDO($dsn, $userName, $password, $options);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Save data in sql database.
     *
     * @param array $data
     *
     * @return bool
     */
    public function save(array $data)
    {
        $this->connection->beginTransaction();

        try {
            $id = Xhgui_Util::getId($data);

            $requestTime = \DateTime::createFromFormat('U u', $data['meta']['request_ts_micro']['sec'].' '.$data['meta']['request_ts_micro']['usec']);

            $infoStatement = $this->connection->prepare('
insert into profiles_info(
    id, url, simple_url, request_time, method, main_ct, main_wt, main_cpu, main_mu, main_pmu, application, version, branch,controller, action, remote_addr, session_id 
) values (
    :id, :url, :simple_url, :request_time, :method, :main_ct, :main_wt, :main_cpu, :main_mu, :main_pmu, :application, :version, :branch, :controller, :action, :remote_addr, :session_id          
)');

            // get some data from meta and save in separate column to make it easier to search/filter
            $infoStatement->execute(array(
                'id' => $id,
                'url'=> $data['meta']['url'],
                'simple_url' => $data['meta']['simple_url'],
                'request_time' => $requestTime->format('Y-m-d H:i:s.u'),
                'main_ct'=> $data['profile']['main()']['ct'],
                'main_wt'=> $data['profile']['main()']['wt'],
                'main_cpu' => $data['profile']['main()']['cpu'],
                'main_mu'=> $data['profile']['main()']['mu'],
                'main_pmu' => $data['profile']['main()']['pmu'],
                'application'=> !empty($data['meta']['application']) ? $data['meta']['application'] : null,
                'version'=> !empty($data['meta']['version']) ? $data['meta']['version'] : null,
                'branch' => !empty($data['meta']['branch']) ? $data['meta']['branch'] : null,
                'controller' => !empty($data['meta']['controller']) ? $data['meta']['controller'] : null,
                'action' => !empty($data['meta']['action']) ? $data['meta']['action'] : null,
                'session_id' => !empty($data['meta']['session_id']) ? $data['meta']['action'] : null,
                'method' => !empty($data['meta']['method']) ? $data['meta']['method'] : Xhgui_Util::getMethod(),
                'remote_addr' => !empty($data['meta']['SERVER']['REMOTE_ADDR']) ? $data['meta']['SERVER']['REMOTE_ADDR'] : null,
            ));

            $profileStatement = $this->connection->prepare('insert into profiles(profile_id, profiles) VALUES(:id, :profiles)');
            $profileStatement->execute(array(
                'id' => $id,
                'profiles' => json_encode($data['profile']),
            ));

            $metaStatement = $this->connection->prepare('insert into profiles_meta(profile_id, meta) VALUES(:id, :meta)');
            $metaStatement->execute(array(
                'id' => $id,
                'meta' => json_encode($data['meta']),
            ));

            $this->connection->commit();

            return true;
        } catch (\Exception $e) {
            $this->connection->rollBack();
            error_log('xhgui - ' . $e->getMessage());
        }
        return false;
    }
}
