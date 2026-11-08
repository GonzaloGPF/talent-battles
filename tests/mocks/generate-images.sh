#!/usr/bin/env bash
#
# Generates the image mocks with FFmpeg, so the repository holds no third-party images.
# Covers and avatars are gradients, designs are fractals and cellular automata. The output is the same on every run.
# Run it inside Sail:
#
#   vendor/bin/sail bash tests/mocks/generate-images.sh

set -euo pipefail

cd "$(dirname "$0")"

# Arguments: output file, then the FFmpeg source that draws the image.
generate() {
    ffmpeg -loglevel error -y -f lavfi -i "$2" -frames:v 1 -map_metadata -1 -c:v libwebp -quality 80 "$1"
}

# A radial gradient from its center color to its edge color, centered at x,y.
radial() {
    echo "gradients=s=$1:type=radial:c0=$2:c1=$3:x0=$4:y0=$5:x1=0:y1=0:seed=1"
}

# A spiral of three colors around the center.
spiral() {
    echo "gradients=s=800x800:type=spiral:c0=$1:c1=$2:c2=$3:nb_colors=3:x0=400:y0=400:x1=800:y1=0:seed=1"
}

rm -f avatars/*.webp designs/*.webp images/*.webp

# Covers of tracks, albums and designs: 800×800
generate images/cover-01.webp "$(radial 800x800 0xffa300 0x7e2553 400 400)"
generate images/cover-02.webp "$(spiral 0x00e436 0x065ab5 0x29adff)"
generate images/cover-03.webp "$(radial 800x800 0x29adff 0x1d2b53 300 300)"
generate images/cover-04.webp "$(spiral 0xff77a8 0xfff024 0x83769c)"
generate images/cover-05.webp "$(radial 800x800 0xa8e61d 0x065a5b 500 300)"
generate images/cover-06.webp "$(spiral 0xff004d 0xffa300 0x1d2b53)"
generate images/cover-07.webp "$(radial 800x800 0xc5b4ff 0x2a1d6e 400 500)"
generate images/cover-08.webp "$(spiral 0x00b5a5 0xfff024 0x065ab5)"

# Designs: 800×800. The cellular automata are drawn at 200×200 and enlarged, so their cells can be seen.
generate designs/carpet.webp "sierpinski=s=800x800:type=carpet:seed=1,lutrgb=r=val*0.2:g=val*0.8:b=val"
generate designs/triangle.webp "sierpinski=s=800x800:type=triangle:seed=2,lutrgb=r=val:g=val*0.3:b=val*0.7"
generate designs/mandelbrot.webp "mandelbrot=s=800x800:start_x=-0.75:start_y=0:start_scale=2.6:maxiter=1000"
generate designs/mandelbrot-zoom.webp "mandelbrot=s=800x800:start_x=-0.7436438870:start_y=-0.1318259042:start_scale=0.02:maxiter=1500"
generate designs/rule-30.webp "cellauto=s=200x200:rule=30:seed=1,scale=800:800:flags=neighbor,lutrgb=r=val*0.95:g=val*0.35:b=val*0.55"
generate designs/rule-90.webp "cellauto=s=200x200:rule=90:seed=3,scale=800:800:flags=neighbor,lutrgb=r=val*0.3:g=val*0.9:b=val*0.5"

# Avatars: 256×256
generate avatars/avatar-01.webp "$(radial 256x256 0xffccaa 0x29adff 128 110)"
generate avatars/avatar-02.webp "$(radial 256x256 0xc2c3c7 0x7e2553 128 110)"
generate avatars/avatar-03.webp "$(radial 256x256 0xfff1e8 0x008751 128 110)"
generate avatars/avatar-04.webp "$(radial 256x256 0xffec27 0xab5236 128 110)"
generate avatars/avatar-05.webp "$(radial 256x256 0xff77a8 0x1d2b53 128 110)"
generate avatars/avatar-06.webp "$(radial 256x256 0xc2fff0 0x065a5b 128 110)"
