################################
### Supervisor Configuration ###
################################
# role :supervisor, "par-vm205.srv.canaltp.fr", {
  # :user => 'nmp-cus-wrk',
  # :no_release => true,
  # :password => "fi6iexee0F"
# }

#########################
### Mtt Configuration ###
#########################

set :mtt_pdf_generator_url, 'http://mhspdfgen-ws.ctp.customer.canaltp.fr/'
set :amqp_server_host, 'amqp-ihm.nmp.customer.canaltp.fr'
set :amqp_server_user, 'guest'
set :amqp_server_pass, 'guest'
set :amqp_server_vhost, 'mtt'
set :amqp_server_port, 5672

##################################
### MediaManager Configuration ###
##################################

set :mtt_configuration_name, 'mtt'
set :mtt_company_name, 'MTT'
set :mtt_storage_type, 'filesystem'
set :mtt_storage_path, '/srv/mhsmedias-ws.ctp.customer.canaltp.fr/'
set :mtt_storage_url, 'http://mhsmedias-ws.ctp.customer.canaltp.fr/'
set :mtt_storage_strategy, 'mtt'
