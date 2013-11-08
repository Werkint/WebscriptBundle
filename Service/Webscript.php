<?php
namespace Werkint\Bundle\WebscriptBundle\Service;

use Werkint\Bundle\WebappBundle\Webapp\Webapp;

/**
 * Webscript.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class Webscript
{

    protected $webapp;
    protected $scripts;
    protected $resdir;

    public function __construct(
        Webapp $webapp,
        array $parameters
    ) {
        $this->webapp = $webapp;

        // parameters
        $this->scripts = $parameters['scripts'];
        $this->resdir = $parameters['resdir'];

        $this->loadPackages();
    }

    protected $packages = [];

    protected function loadPackages()
    {
        foreach (file($this->scripts . '/.packages') as $package) {
            $this->packages[trim($package)] = trim($package);
        }
    }

    /**
     * Attaches package
     * @param string $name Package name
     * @param array  $vars Variables to pass
     * @throws \Exception
     */
    public function attach($name, array $vars = [])
    {
        if (!isset($this->packages[$name])) {
            throw new \Exception('Package not found: ' . $name);
        }
        if ($this->wasLoaded($name)) {
            // Package already was loaded
            return;
        }
        // Package data
        $path = $this->scripts . '/' . $name;
        $meta = parse_ini_file($path . '/.package.ini');

        // Dependencies
        foreach (explode(',', $meta['deps']) as $dep) {
            if (!($dep = trim($dep))) {
                continue;
            }
            $this->attach($dep);
        }

        // Scripts
        foreach (explode(',', $meta['files']) as $file) {
            if (!($file = trim($file))) {
                continue;
            }
            $this->webapp->attachFile($path . '/' . $file);
        }

        // Resources
        foreach (explode(',', $meta['res']) as $file) {
            if (!($file = trim($file))) {
                continue;
            }
            $this->loadRes($path . '/' . $file, $file, $name);
        }

        // Variables
        foreach ($vars as $name => $val) {
            // TODO: pass only to package
            $this->webapp->addVar($name, $val);
        }

        // Loaded successfully
        $this->setLoaded($name);
    }

    // -- Static resources ---------------------------------------

    protected $staticRes = [];

    protected function loadRes($path, $name, $bundle)
    {
        if (!isset($this->staticRes[$bundle])) {
            $this->staticRes[$bundle] = [];
        } else if (isset($this->staticRes[$bundle][$name])) {
            return;
        }
        $this->staticRes[$bundle][$name] = $path;
        $imgpath = $this->resdir . '/' . $bundle;
        if (!file_exists($imgpath)) {
            mkdir($imgpath);
        }
        $imgpath .= '/' . $name;
        if (file_exists($imgpath)) {
            return;
        }
        try {
            symlink($path, $imgpath);
        } catch (\Exception $e) {
            throw new \Exception(
                'Cannot symlink  "' . $path . '" to "' . $imgpath . '"'
            );
        }
    }

    // -- Loaded ---------------------------------------

    protected function wasLoaded($name)
    {
        return $this->webapp->getLoader()->isPackageLoaded($name);
    }

    protected function setLoaded($name)
    {
        if ($this->wasLoaded($name)) {
            throw new \Exception('Script already was loaded');
        }
        return $this->webapp->getLoader()->addPackage($name);
    }

}