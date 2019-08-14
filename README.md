# Optimised images for SilverStripe

A module to automatically optimise/compress both uploaded as well as any resampled
(cropped, scaled etc) images in SilverStripe. Images (JPG, PNG & GIF) are automatically
optimised, provided you have the correct binaries installed (see "Installation" below).

The module overrides the default `FlysystemAssetStore` to first optimise the image
before adding the image to the store. It works transparently, and includes a task to
optimise all existing (previously created) images.


## Requirements

- `silverstripe/assets` ^4.0
- [spatie/image-optimizer](https://github.com/spatie/image-optimizer) - automatically installed
- JpegOptim, Optipng, Pngquant 2 & Gifsicle binaries (see below)


## Optimisation tools

The module uses [spatie/image-optimizer](https://github.com/spatie/image-optimizer) and will use the
following optimisers if they are both present and in your default path on your system:

- [JpegOptim](https://github.com/tjko/jpegoptim)
- [Optipng](http://optipng.sourceforge.net/)
- [Pngquant 2](https://pngquant.org/)
- [Gifsicle](http://www.lcdf.org/gifsicle/)


## Installation

```shell
composer require axllent/silverstripe-image-optimiser
```

### Installing the utilities on Ubuntu:

```bash
sudo apt-get install jpegoptim optipng pngquant gifsicle
```


### Installing the utilities on Alpine Linux:

```bash
apk add jpegoptim optipng pngquant gifsicle
```


## Usage

Assuming you have the necessary binaries installed, it should "just work" with the default settings
once you have flushed your SilverStripe installation.

~~If you need to optimise any previously-uploaded images, see `dev/tasks/ImageOptimiser`.
This should only be needed once for older images as any new images uploaded will
get automatically optimised.~~  Currently this has been disabled as it causes corruption due to filehash mismatches (and upgrade issues to >= 4.4.0). Once I have thought of a more elgant method I will enable it. In the meantime pull requests are welcome.

For custom optimisation settings, please refer to the
[Configuration documentation](docs/en/Configuration.md).
