################################
### Supervisor Configuration ###
################################
role :supervisor, "nmp-prd-wrk1.canaltp.prod", {
  :user => 'nmp-prd-wrk',
  :no_release => true,
  :password => "yohzohhuot"
}

#########################
### Mtt Configuration ###
#########################

set :mtt_pdf_generator_url, 'http://mhspdfgen-ws.ctp.prod.canaltp.fr/'
set :amqp_server_host, 'amqp-ihm.nmp.prod.canaltp.fr'
set :amqp_server_user, 'mtt'
set :amqp_server_pass, 'hee4ohQuei'
set :amqp_server_vhost, 'mtt'
set :amqp_server_port, 5672

##################################
### MediaManager Configuration ###
##################################

set :mtt_configuration_name, 'mtt'
set :mtt_company_name, 'MTT'
set :mtt_storage_type, 'filesystem'
set :mtt_storage_path, '/srv/mhsmedias-ws.ctp.prod.canaltp.fr/'
set :mtt_storage_url, 'http://mhsmedias-ws.ctp.prod.canaltp.fr/'
set :mtt_storage_strategy, 'CanalTP\MediaManager\Strategy\DefaultStrategy'
set :mtt_template_storage_path, "#{shared_path}/mtt/template"
