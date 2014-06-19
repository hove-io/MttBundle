namespace :mtt do
    desc ""
    task :deploy, :roles => :app, :except => { :no_release => true } do
    end
end

after "post:composer", "mtt:deploy"
