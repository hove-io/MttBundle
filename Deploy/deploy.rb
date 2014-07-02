namespace :mtt do
    desc "Restart workers on supervisor server"
    task :restart, :roles => :supervisor do
        run "supervisorctl restart all"
    end
end

after "deploy:create_symlink", "mtt:restart"
