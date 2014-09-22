################################
### Supervisor Configuration ###
################################
role :supervisor, "10.2.16.70", {
  :user => 'sam',
  :no_release => true,
  :password => "iy9>Y&8E123"
}

#########################
### Mtt Configuration ###
#########################

set :mtt_pdf_generator_url, 'http://mhspdfgen-ws.ctp.dev.canaltp.fr/'
set :amqp_server_host, '10.2.16.70'
set :amqp_server_user, 'guest'
set :amqp_server_pass, 'guest'
set :amqp_server_vhost, 'mtt/development'
set :amqp_server_port, 5672

##################################
### MediaManager Configuration ###
##################################

set :mtt_configuration_name, 'mtt'
set :mtt_company_name, 'MTT'
set :mtt_storage_type, 'filesystem'
set :mtt_storage_path, '/srv/sam/nmm-ihm.dev.canaltp.fr/current/web/uploads/'
set :mtt_storage_url, 'http://nmm-ihm.dev.canaltp.fr/uploads/'
set :mtt_storage_strategy, 'CanalTP\MediaManager\Strategy\DefaultStrategy'
