# Configuration

There probably is very little reason to change the default optimisation configuration.

Below is an example config with the default options. Each optimisation "chain" is specified by the
full ClassName, and the optional command line options for the binary. These chains use the default
[spatie/image-optimizer](https://github.com/spatie/image-optimizer) classes and values,
so if you wish to add your own classes then please refer to the
[spatie/image-optimizer documentation](https://github.com/spatie/image-optimizer#writing-a-custom-optimizers).

```yaml
Axllent\ImageOptimiser\Flysystem\FlysystemAssetStore:
  chains:
    Spatie\ImageOptimizer\Optimizers\Jpegoptim:
      - "--max=85"
      - "--all-progressive"
    Spatie\ImageOptimizer\Optimizers\Pngquant:
      - "--force"
    Spatie\ImageOptimizer\Optimizers\Optipng:
      - "-i0"
      - "-o2"
      - "-quiet"
    Spatie\ImageOptimizer\Optimizers\Gifsicle:
      - "-b"
      - "-O3"
```
