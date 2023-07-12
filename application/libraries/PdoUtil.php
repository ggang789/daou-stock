<?php

class PdoUtil {

    /**
     * @var array $config
     */
    private $config;

    /**
     * @var PDO $instance
     */
    private $instance;

    /**
     * Constructor
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get the connection of database
     *
     * @return PDO|null
     */
    public function getConnection(): ?PDO
    {
        if (
            !empty($this->instance)
            && $this->instance instanceof PDO
        ) {
            return $this->instance;
        }

        try {
            return $this->instance = new PDO(
                $this->getDataSourceName($this->config),
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => true,
                    PDO::ATTR_PERSISTENT => false
                ]
            );
        } catch (PDOException $e) {
            die("DB connect error: {$e->getMessage()}");
        }
    }

    /**
     * Get the data source name
     *
     * @param array $config
     * @return string
     */
    private function getDataSourceName(array $config): string
    {
        return join(':', [
            'mysql',
            join(';', [
                join('=', ['host', $config['hostname']]),
                join('=', ['port', $config['port']]),
                join('=', ['dbname', $config['database']]),
                join('=', ['charset', 'utf8mb4']),
            ])
        ]);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->instance = null;
    }

}
