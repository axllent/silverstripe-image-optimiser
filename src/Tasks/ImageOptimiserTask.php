<?php
namespace Axllent\ImageOptimiser\Tasks;

use Axllent\ImageOptimiser\Flysystem\FlysystemAssetStore as IOFlysystemAssetStore;
use ReflectionMethod;
use SilverStripe\Assets\Flysystem\FlysystemAssetStore;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use Spatie\ImageOptimizer\OptimizerChain;

/**
 * Task to optimise all SilverStripe generated images
 *
 * It's a hack based on a hack - https://gist.github.com/blueo/6598bc349b406cf678f9a8f009587a95
 * as SilverStripe doesn't have a native public way of returning Image Variants.
 *
 * @license: MIT-style license http://opensource.org/licenses/MIT
 * @author:  Techno Joy development team (www.technojoy.co.nz)
 */
class ImageOptimiserTask extends BuildTask
{
    /**
     * Task Title
     *
     * @var string $title Shown in the overview on the {@link TaskRunner}
     * HTML or CLI interface. Should be short and concise, no HTML allowed.
     */
    protected $title = 'Optimise All Images';

    /**
     * Task Description
     *
     * @var string $description Describe the implications the task has,
     * and the changes it makes. Accepts HTML formatting.
     */
    protected $description = 'Optimises all previously uploaded images';

    /**
     * Set a custom url segment (to follow dev/tasks/)
     *
     * @config
     *
     * @var string
     */
    private static $segment = 'ImageOptimiser';

    /**
     * Enable the task
     *
     * @var bool $enabled If set to FALSE, keep it from showing in the list
     * and from being executable through URL or CLI.
     */
    protected $enabled = false;

    /**
     * Implement this method in the task subclass to
     * execute via the TaskRunner
     *
     * @param HTTPRequest $request HTTP request
     *
     * @return String
     */
    public function run($request)
    {
        $all_files = $this->_findOrOptimiseAllImagesAndVariants(false);

        if ($request->getVar('run') || Director::is_cli()) {

        } else {
            print '<p>There are ' . count($all_files) . ' images and image variants.</p>
                <p>Please ensure that you have backups before running this task as
                this will overwrite each image (if sufficiently optimised).</p>
                <p>Depending on how many images you have and how big they are,
                this process can take a significant amount of time, which may time
                out in your browser.<p>
                <p>Running this task on the command line is strongly recommended
                (<code>vendor/bin/sake dev/tasks/ImageOptimiser</code>)</p>
                <p><a href="?run=1">Click here to optimise all images via
                your browser.</a></p>';
            exit;
        }

        set_time_limit(0);

        $this->_findOrOptimiseAllImagesAndVariants(true);
    }

    /**
     * Find All Images And Variants
     * Optionally optimise
     *
     * @param Boolean $optimise Whether to optimise the images
     *
     * @return Array Array of all images and variants on FlysystemAssetStore
     */
    private function _findOrOptimiseAllImagesAndVariants($optimise = false)
    {
        $images = Image::get();

        // warning - super hacky as accessing private methods
        $getFileSystem = new ReflectionMethod(FlysystemAssetStore::class, 'getFilesystemFor');
        $getID         = new ReflectionMethod(FlysystemAssetStore::class, 'getFileID');
        $findVariants  = new ReflectionMethod(FlysystemAssetStore::class, 'findVariants');
        $store         = Injector::inst()->get(AssetStore::class);

        $getFileSystem->setAccessible(true);
        $getID->setAccessible(true);

        $chains = Config::inst()->get(IOFlysystemAssetStore::class, 'chains');

        // create optimizer
        $optimizer = new OptimizerChain;
        foreach ($chains as $class => $options) {
            $optimizer->addOptimizer(
                new $class($options)
            );
        }

        $images = Image::get();
        $files  = [];

        $total_kb_saved = 0;

        foreach ($images as $img) {
            $assetValues = $img->File->getValue();
            $flyID       = $getID->invoke($store, $assetValues['Filename'], $assetValues['Hash']);
            $system      = $getFileSystem->invoke($store, $flyID);
            $findVariants->setAccessible(true);

            if (empty($assetValues)) {
                continue;
            }

            foreach ($findVariants->invoke($store, $flyID, $system) as $variant) {
                array_push($files, $variant);

                if ($optimise) {
                    $raw       = $system->read($variant);
                    $orig_size = strlen($raw);

                    // write to tmp file and optimise
                    $optim = TEMP_PATH . DIRECTORY_SEPARATOR . 'optim_' . uniqid() . '.' . $img->Name;
                    @file_put_contents($optim, $raw);

                    $optimizer->optimize($optim);

                    $optim_size = filesize($optim);

                    if ($optim_size < $orig_size && $saved = round(($orig_size - $optim_size) / 1024)) {
                        $raw = $system->update($variant, file_get_contents($optim));
                        DB::alteration_message("$variant ($optim_size/$orig_size) -$saved KB", 'changed');
                        $total_kb_saved = $total_kb_saved + $saved;
                    }

                    @unlink($optim);
                }
            }
        }

        if ($optimise) {
            $nr_files = count($files);
            DB::alteration_message("Processed $nr_files files, $total_kb_saved KB saved", 'changed');
        }

        return $files;
    }
}
