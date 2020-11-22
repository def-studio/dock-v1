<?php


    namespace App\Containers;


    use App\Exceptions\ContainerException;
    use Illuminate\Contracts\Filesystem\Filesystem;
    use Illuminate\Support\Facades\Storage;

    class Php extends Container{

        const VERSIONS = [
          '7.0' => 'https://gitlab.com/defstudio/docker/php-7.0.git',
          '7.1' => 'https://gitlab.com/defstudio/docker/php-7.1.git',
          '7.4' => 'https://gitlab.com/defstudio/docker/php.git',
          'latest' => 'https://gitlab.com/defstudio/docker/php.git',
        ];


        protected string $service_name = "php";


        protected array $service_definition = [
            'restart'     => 'unless-stopped',
            'working_dir' => '/var/www',
            'build'       => [
                'context' => self::VERSIONS['latest'],
                'args'    => [
                    'ENABLE_XDEBUG' => 0,
                    'ENABLE_LIBREOFFICE_WRITER' => '${ENABLE_LIBREOFFICE_WRITER:-0}',
                ],
            ],
            'expose'      => [9000],
        ];

        protected array $volumes = [
            self::HOST_SRC_VOLUME_PATH => '/var/www',
        ];

        public function set_version($version){
            if(empty(self::VERSIONS[$version])) return;

            $this->set_service_definition('build.context', self::VERSIONS[$version]);
        }

        public function enable_xdebug(): self{
            $this->set_service_definition('build.args.ENABLE_XDEBUG', 1);
            return $this;
        }

        public function disable_xdebug(): self{
            $this->set_service_definition('build.args.ENABLE_XDEBUG', 1);
            return $this;
        }

        /**
         * Php constructor.
         *
         * @throws ContainerException
         */
        public function __construct(){
            parent::__construct();
            $this->set_user_uid();
        }

    }
