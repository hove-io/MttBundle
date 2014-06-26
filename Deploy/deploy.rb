namespace :mtt do
    desc "Restart workers on supervisor server"
    task :restart, :roles => :supervisor, :except => { :no_release => true } do
    end
end

after "post:composer", "mtt:restart"
