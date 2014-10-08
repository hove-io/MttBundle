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
        run "cd #{current_release}/vendor/canaltp/mtt-bundle/CanalTP/MttBundle/Resources/public/img && rm uploads && ln -s #{shared_path}/mtt/templates/img uploads"
        run "cd #{current_release}/vendor/canaltp/mtt-bundle/CanalTP/MttBundle/Resources/public/css && rm uploads && ln -s #{shared_path}/mtt/templates/css uploads"
        run "cd #{current_release}/vendor/canaltp/mtt-bundle/CanalTP/MttBundle/Resources/views/Layouts && rm uploads && ln -s #{shared_path}/mtt/templates/twig uploads"
    end
end

after "deploy:create_symlink", "mtt:restart"
after "post:composer", "mtt:templates_symlinks"
after "deploy:rollback", "mtt:restart"
