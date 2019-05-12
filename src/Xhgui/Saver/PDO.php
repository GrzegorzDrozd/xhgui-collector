<?php
/**
 * PDO handler to save profiler data
 */
class Xhgui_Saver_PDO implements \Xhgui_Saver_Interface {

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
    public function __construct($dsn, $userName, $password, $options = [])
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
            $id = Xhgui_Util::getId(true);

            $requestTime = \DateTime::createFromFormat('U u', $data['meta']['request_ts_micro']['sec'].' '.$data['meta']['request_ts_micro']['usec']);

            $profileStatement = $this->connection->prepare('insert into profiles(profile_id, profiles) VALUES(:id, :profiles)');
            $profileStatement->execute([
                'id'        => $id,
                'profiles'  => json_encode($data['profile']),
            ]);

            $metaStatement = $this->connection->prepare('insert into profiles_meta(profile_id, meta) VALUES(:id, :meta)');
            $metaStatement->execute([
                'id'        => $id,
                'meta'      => json_encode($data['meta']),
            ]);

            $infoStatement = $this->connection->prepare('
insert into profiles_info(
    id, url, request_time, method, main_ct, main_wt, main_cpu, main_mu, main_pmu, application, version, branch,controller, action 
) values (
    :id, :url, :request_time, :method, :main_ct, :main_wt, :main_cpu, :main_mu, :main_pmu, :application, :version, :branch, :controller, :action          
)');
            $infoStatement->execute([
                'id'            => $id,
                'url'           => $data['meta']['simple_url'],
                'request_time'  => ($requestTime->format('Y-m-d H:i:s.u')),
                'method'        => (php_sapi_name()==='cli' ? 'CLI' : $_SERVER['REQUEST_METHOD']),
                'main_ct'       => $data['profile']['main()']['ct'],
                'main_wt'       => $data['profile']['main()']['wt'],
                'main_cpu'      => $data['profile']['main()']['cpu'],
                'main_mu'       => $data['profile']['main()']['mu'],
                'main_pmu'      => $data['profile']['main()']['pmu'],
                'application'   => $data['meta']['application'],
                'version'       => $data['meta']['version'],
                'branch'        => $data['meta']['branch'],
                'controller'    => $data['meta']['controller'],
                'action'        => $data['meta']['action']
            ]);
            $this->connection->commit();

            return true;
        } catch (\Exception $e) {
            $this->connection->rollBack();
            error_log('xhgui - ' . $e->getMessage());
        }
        return false;
    }
}