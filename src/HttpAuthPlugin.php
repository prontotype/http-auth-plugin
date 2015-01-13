<?php namespace Prontotype\Plugins\HttpAuth;

use Prontotype\Event;
use Prontotype\Plugins\AbstractPlugin;
use Prontotype\Plugins\PluginInterface;

class HttpAuthPlugin extends AbstractPlugin implements PluginInterface
{
    public function getConfig()
    {
        return 'config/config.yml';
    }

    public function register()
    {
        $events = $this->container->make('prontotype.events');
        $events->emit(Event::named('basic-auth.register.start'));
        
        $this->container->alias('httpauth', 'Prontotype\Plugins\HttpAuth\HttpAuth')->share('httpauth');   
        $auth = $this->container->make('httpauth');

        $events->addListener('services.boot.end', function() use ($auth) {
            $auth->secure();
        });
        
        $events->emit(Event::named('basic-auth.register.end'));
    }

    public function getGlobals()
    {
        return array(
            
        );
    }

}