# Optimised images for Silverstripe

A module to automatically optimise/compress uploaded as well as any resampled
(cropped, scaled etc) images in Silverstripe. Images (JPG, PNG & GIF) are automatically
optimised, provided you have the correct binaries installed (see "Installation" below).

The module overrides the default `FlysystemAssetStore` to and transparently optimises
the image before adding the image to the store.

## Requirements

- `silverstripe/framework` ^4.0 || ^5.0 || ^6.0
- `silverstripe/assets` ^1.10 || ^2.0 || ^3.0
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
once you have flushed your Silverstripe installation.

For custom optimisation settings, please refer to the
[Configuration documentation](docs/en/Configuration.md).
