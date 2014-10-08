################################
### Supervisor Configuration ###
################################
role :supervisor, "par-vm197.srv.canaltp.fr", {
  :user => 'nmp-int-wrk',
  :no_release => true,
  :password => "om5Baeth7X"
}

#########################
### Mtt Configuration ###
#########################

set :mtt_pdf_generator_url, 'http://mhspdfgen-ws.ctp.internal.canaltp.fr/'
set :amqp_server_host, 'amqp-ihm.nmp.internal.canaltp.fr'
set :amqp_server_user, 'mtt'
set :amqp_server_pass, 'mtt'
set :amqp_server_vhost, 'mtt/internal'
set :amqp_server_port, 5672

##################################
### MediaManager Configuration ###
##################################

set :mtt_configuration_name, 'mtt'
set :mtt_company_name, 'MTT'
set :mtt_storage_type, 'filesystem'
set :mtt_storage_path, '/srv/www/htdocs/sam/nmm-ihm.mutu.internal.canaltp.fr/current/web/uploads/'
set :mtt_storage_url, 'http://nmm-ihm.mutu.internal.canaltp.fr/uploads/'
set :mtt_storage_strategy, 'CanalTP\MediaManager\Strategy\DefaultStrategy'
set :mtt_template_upload_path, "/srv/www/htdocs/sam/nmm-ihm.mutu.internal.canaltp.fr/shared/mtt"
