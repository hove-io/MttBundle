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
set :mtt_storage_path, '/srv/www/mhsmedias-ws.ctp.prod.canaltp.fr/'
set :mtt_storage_url, 'http://mhsmedias-ws.ctp.prod.canaltp.fr/'
set :mtt_storage_strategy, 'mtt'