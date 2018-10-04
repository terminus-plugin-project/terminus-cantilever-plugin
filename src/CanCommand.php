<?php
namespace Pantheon\TerminusCantilever;

use Pantheon\Terminus\Commands\Site\SiteCommand;

class CanCommand extends SiteCommand
{
	/**
	 * Grab all sites.
	 *
	 * @authorize
	 *
	 * @command can
	 *
	 * @option env Choose site environment
	 * @option level Choose site service level (free,basic,pro,business,performance)
	 * @option frame Choose site framework (drupal,drupal8,wordpress)
	 * @option tags Choose site tags (development,enterprise,terminated)
	 * @option org Name of organization/membership site belongs to
	 * @option command Add your command (use [site] for name.env, [name] for site name, and [env] for environment)
	 *
	 * terminus can --env=live --level='pro,business,performance' --frame='drupal,drupal8' --command='terminus drush [site] pml|grep redis'
	 * terminus can --env=live --frame='wordpress' --command='terminus wp [site] option get home'
	 *
	 */
	public function cantilever($options = ['env' => 'dev', 'level' => null, 'frame' => 'drupal', 'tags' => null, 'org' => null, 'command' => null])
	{
		//show initialize
		$this->log()->notice("Cantilever initializing...");

		//list sites
		$sites = $this->sites->serialize();

		if (empty($sites)) {
			$this->log()->notice('You have no sites.');
		} else {
			if (isset($options['env'])) {
				echo "environment: " . $options['env']."\n";
			}
			if (isset($options['tags'])) {
				echo "tags: " . $options['tags']."\n";
			}
			if (isset($options['org'])) {
				echo "organization: " . $options['org']."\n";
			}
			if (isset($options['env']) || isset($options['tags']) || isset($options['org'])) {
				echo "----------\n\n";
			}

			//site loop
			foreach ($sites as $key => $site) {
//            	print_r($site);

				//remove non selected sites
				if (isset($options['level'])) {
					$level = explode(",", $options['level']);
					if (!in_array($site['service_level'], $level, true)) {
						unset($sites[$key]);
					}
				}

				if (isset($options['frame'])) {
					$frame = explode(",", $options['frame']);
					if (!in_array($site['framework'], $frame, true)) {
						unset($sites[$key]);
					}
				}

				if (isset($options['tags'])) {
					$tags = explode(",", $options['tags']);
					if (!isset($site['tags']) || (isset($site['tags']) && !in_array($site['tags'], $tags, true))) {
						unset($sites[$key]);
					}
				}

				if (isset($options['org'])) {
					if (preg_match("/(\b" . $options['org'] . "\b)(\n|,|$)/", $site['memberships']) == false) {
						unset($sites[$key]);
					}
				}

				//run
				if (empty($sites)) {
					$this->log()->notice('You have no sites.');
				} else {
					if (isset($sites[$key])) {
						//print site
						echo $site['name'] . "\n";

						//compile command
						if (isset($options['command'])) {
							$command = $options['command'];
							$command = str_replace("[site]", $site['name'] . "." . $options['env'], $command);
							$command = str_replace("[name]", $site['name'], $command);
							$command = str_replace("[env]", $options['env'], $command);

							echo "----------\n";
							$output = shell_exec($command);
							if ($output == '') {
								$output = "** no results **\n";
							}

							//print output
							echo $output . "\n";
						}
					}
				}
			}
		}
	}
}
