namespace :mtt do
    desc "Restart workers on supervisor server"
    task :restart, :roles => :supervisor do
        run "sudo supervisorctl restart all"
    end
end

namespace :mtt do
    desc "Simlinks for templates"
    task :templates_symlinks, :roles => :supervisor do
        run "mkdir -p #{shared_path}/mtt/templates/img"
        run "mkdir -p #{shared_path}/mtt/templates/css"
        run "mkdir -p #{shared_path}/mtt/templates/twig"
        run "mkdir -p #{shared_path}/mtt/archives"
        run "cd #{current_release}/vendor/canaltp/mtt-bundle/CanalTP/MttBundle/Resources/public/img && (test ! -L uploads || rm uploads) && ln -s #{shared_path}/mtt/templates/img uploads"
        run "cd #{current_release}/vendor/canaltp/mtt-bundle/CanalTP/MttBundle/Resources/public/css && (test ! -L uploads || rm uploads) && ln -s #{shared_path}/mtt/templates/css uploads"
        run "cd #{current_release}/vendor/canaltp/mtt-bundle/CanalTP/MttBundle/Resources/views/Layouts && (test ! -L uploads || rm uploads) && ln -s #{shared_path}/mtt/templates/twig uploads"
        run "setfacl -m u:www-data:rwX -m u:`whoami`:rwX #{shared_path}/mtt"
        run "setfacl -d -m u:www-data:rwX -m u:`whoami`:rwX #{shared_path}/mtt"

        run "cd #{current_release} && ./app/console assets:install --symlink"
        run "#{current_release}/app/console assetic:dump"
    end
end

after "deploy:create_symlink", "mtt:restart"
after "post:composer", "mtt:templates_symlinks"
after "deploy:rollback", "mtt:restart"
