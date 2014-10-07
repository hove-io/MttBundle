namespace :mtt do
    desc "Restart workers on supervisor server"
    task :restart, :roles => :supervisor do
        run "sudo supervisorctl restart all"
    end
end

namespace :mtt do
    desc "Simlinks for templates"
    task :templates_symlinks, :roles => :supervisor do
        run "mkdir -p #{mtt_storage_path}template/img"
        run "mkdir -p #{mtt_storage_path}template/css"
        run "mkdir -p #{mtt_storage_path}template/twig"
        run "cd #{current_release}/vendor/canaltp/mtt-bundle/CanalTP/MttBundle/Resources/public/img && ln -s #{mtt_storage_path}template/img uploads"
        run "cd #{current_release}/vendor/canaltp/mtt-bundle/CanalTP/MttBundle/Resources/public/css && ln -s #{mtt_storage_path}template/css uploads"
        run "cd #{current_release}/vendor/canaltp/mtt-bundle/CanalTP/MttBundle/Resources/views/Layouts && ln -s #{mtt_storage_path}template/twig uploads"
    end
end

after "deploy:create_symlink", "mtt:restart"
after "post:composer", "mtt:templates_symlinks"
after "deploy:rollback", "mtt:restart"
