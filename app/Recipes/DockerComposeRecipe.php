<?php


	namespace App\Recipes;


	use App\Containers\Container;
    use App\Exceptions\ContainerException;
    use App\Exceptions\DuplicateServiceException;
    use App\Services\DockerService;
    use Illuminate\Console\Command;
    use Illuminate\Contracts\Container\BindingResolutionException;

    abstract class DockerComposeRecipe{

	    const LABEL = null;

	    const ENV_FILE_TEMPLATE = null;

        protected $docker_service;

        /** @var Container[] $services */
        private $containers = [];

        private $exposed_hosts = [];

        private $exposed_addresses = [];


        public function __construct(DockerService $docker_service){
            $this->docker_service = $docker_service;
        }

        /**
         * Initialize the recipe
         * @param Command $parent_command
         * @return int
         */
        public abstract function init(Command $parent_command): int;

	    public function label(){
	        return static::LABEL;
        }

        /**
         * Compute and builds the recipe
         * @return mixed
         */
	    public abstract function build();

        /**
         * @throws DuplicateServiceException
         * @throws ContainerException
         */
        public function setup(){
            foreach($this->containers as $container){
                $this->docker_service->add_container($container);
            }
        }

        /**
         * Retrieves commands defined by the recipe and by its containers
         * @return string[]
         */
        public function commands(): array{
            $commands = $this->recipe_commands();

            foreach($this->containers as $container){
                foreach($container->commands() as $command){
                    $commands[] = $command;
                }
            }
            return array_unique($commands);
        }

        /**
         * Retrieves commands defined by the recipe
         * @return string[]
         */
        protected abstract function recipe_commands(): array;

        /**
         * Returns hosts exposed by the recipe
         * @return array
         */
        public function hosts(): array{
            return array_unique($this->exposed_hosts);
        }

        /**
         * Returns the reachable urls of the services
         * @return array
         */
        public function urls(): array{
            return $this->exposed_addresses;
        }

        /**
         * Add a container to the recipe
         * @param string $class
         * @param array $arguments
         * @return Container
         * @throws BindingResolutionException
         */
        protected function add_container(string $class, array $arguments = []): Container{
            $container = app()->make($class, $arguments);
            $this->containers[] = $container;
            return $container;
        }

        /**
         * Add an host exposed by the recipe
         * @param $hostname
         */
        protected function add_exposed_host($hostname){
            $this->exposed_hosts[] = $hostname;
        }

        /**
         * Add a reachable address for one of the recipe services
         * @param string $label
         * @param string $protocol
         * @param $uri
         * @param $port
         */
        protected function add_exposed_address(string $label, string $protocol, $uri, $port){
            if($port==80||$port==443){
                $port = "";
            }else{
                $port = ":$port";
            }
            $this->exposed_addresses[$label] = "$protocol://{$uri}{$port}";
        }

    }
