<?php
namespace Pantheon\TerminusCantilever;

use Pantheon\Terminus\Collections\Sites;
use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;

class CanCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;

    /**
     * Grab all sites.
     *
     * @authorize
     *
     * @command can
     *
     * @option env Choose site environment (dev,test,live,multidev)
     * @option plan Choose site plan (sandbox,basic,performance small,performance medium,elite)
     * @option frame Choose site framework (drupal,drupal8,wordpress)
     * @option tags Choose site tags (development,enterprise,terminated)
     * @option org Name of organization/membership site belongs to
     * @option command Add your command (use [site] for name.env, [name] for site name, and [env] for environment)
     *
     * terminus can --env=live --plan='basic,performance small,performance medium,elite' --frame='drupal,drupal8' --command='terminus drush [site] pml|grep redis'
     * terminus can --env=live --frame='wordpress' --command='terminus wp [site] option get home'
     *
     */
    public function cantilever($options = ['env' => 'dev', 'plan' => null, 'frame' => 'drupal', 'tags' => null, 'org' => null, 'command' => null])
    {
        $sites_exist = false;

        //show initialize
        $this->log()->notice("Cantilever initializing...");

        //list sites
        $sites = $this->sites->serialize();

        if (empty($sites)) {
            $this->log()->notice('You have no sites.');
            exit;
        }
        if (isset($options['env'])) {
            echo "environment: " . $options['env'] . "\n";
        } else {
            echo "environment: dev\n";
        }
        if (isset($options['plan'])) {
            echo "plan: " . $options['plan']."\n";
        }
        if (isset($options['frame'])) {
            echo "framework: " . $options['frame'] . "\n";
        } else {
            echo "framework: drupal\n";
        }
        if (isset($options['tags'])) {
            echo "tags: " . $options['tags']."\n";
        }
        if (isset($options['org'])) {
            echo "organization: " . $options['org'] . "\n";
        }
        if (isset($options['command'])) {
            echo "command: " . $options['command'] . "\n";
        }

        //site loop
        foreach ($sites as $key => $site) {

            //select available environments
            $all_envs = [];
            $envs = isset($options['env']) ? explode(',', $options['env']) : ['dev'];
            if ($environments = $this->getSite($site['name'])->getEnvironments()->serialize()) {
                foreach ($environments as $environment) {
                    $env = $environment['id'];
                    $all_envs[] = $env;
                    if (!$environment['initialized'] && ($index = array_search($env, $envs)) !== false) {
                        unset($envs[$index]);
                    }
                }
            } else {
                $envs = [];
            }

            //remove unknown environments
            if (isset($envs)) {
                foreach ($envs as $env) {
                    if (!in_array($env, $all_envs) && ($index = array_search($env, $envs)) !== false) {
                        unset($envs[$index]);
                    }
                }
            }

            //remove sites with no environments
            if (!isset($envs)) {
                unset($sites[$key]);
            }

            //remove non selected sites
            if (isset($options['plan'])) {
                $plan = explode(',', strtolower(trim($options['plan'])));
                if (!in_array(strtolower($site['plan_name']), $plan)) {
                    unset($sites[$key]);
                }
            }

            if (isset($options['frame'])) {
                $frame = explode(',', strtolower(trim($options['frame'])));
                if (!in_array(strtolower($site['framework']), $frame)) {
                    unset($sites[$key]);
                }
            }

            if (isset($options['tags'])) {
                $tags = explode(',', strtolower(trim($options['tags'])));
                if (!isset($site['tags']) || (isset($site['tags']) && !in_array(strtolower($site['tags']), $tags))) {
                    unset($sites[$key]);
                }
            }

            if (isset($options['org'])) {
                if (preg_match("/(\b" . $options['org'] . "\b)(\n|,|$)/", $site['memberships']) == false) {
                    unset($sites[$key]);
                }
            }

            //run
            if (isset($sites[$key])) {
                //compile command
                if (isset($options['command'])) {
                    foreach ($envs as $env) {
                        //print site.env
                        $site_env = $site['name'] . '.' . $env;
                        $chars = strlen($site_env);
                        echo str_repeat('=', $chars) . "\n";
                        echo $site_env . "\n";
                        echo str_repeat('=', $chars) . "\n";
                        $command = $options['command'];
                        $command = str_replace("[site]", $site_env, $command);
                        $command = str_replace("[name]", $site['name'], $command);
                        $command = str_replace("[env]", $env, $command);
                        $output = $this->execute($command);
                        echo $output . "\n";
                        $sites_exist = true;
                    }
                }
            }
        }
        if (!$sites_exist) {
            $this->log()->notice('You have no sites that match this filter criteria.');
        }
    }

    /**
     * Executes the command.
     */
    protected function execute($cmd)
    {
        $process = proc_open(
            $cmd,
            [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR,
            ],
            $pipes
        );
        proc_close($process);
    }
}
