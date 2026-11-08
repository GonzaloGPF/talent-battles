#!/usr/bin/env bash
#
# Generates the audio mocks with FFmpeg, so the repository holds no third-party music.
# Each clip is a kick on every beat, a hi-hat between beats and a soft chord, at a known tempo.
# The output is the same on every run. Run it inside Sail:
#
#   vendor/bin/sail bash tests/mocks/generate-audio.sh

set -euo pipefail

cd "$(dirname "$0")"

# The expression of a clip: its tempo in BPM and the root frequency of its chord in Hz.
expression() {
    local period="60/$1" root=$2

    echo "0.7*sin(2*PI*55*t)*exp(-12*mod(t,$period))" \
        "+ 0.12*(2*random(0)-1)*exp(-90*mod(t+$period/2,$period))" \
        "+ 0.05*(sin(2*PI*$root*t)+sin(2*PI*$root*1.26*t)+sin(2*PI*$root*1.5*t))"
}

# Arguments: output file, tempo, chord root, seconds, then the encoder options.
generate() {
    local file=$1 bpm=$2 root=$3 seconds=$4
    shift 4

    ffmpeg -loglevel error -y -f lavfi -i "aevalsrc='$(expression "$bpm" "$root")':s=44100:d=$seconds" -ac 1 -map_metadata -1 "$@" "$file"
}

rm -f beats/*.mp3 samples/*.wav

# Beats: 20 seconds, mono MP3. The tests use the one at 120 BPM.
generate beats/beat-090-bpm.mp3 90 196 20 -c:a libmp3lame -b:a 96k
generate beats/beat-095-bpm.mp3 95 247 20 -c:a libmp3lame -b:a 96k
generate beats/beat-100-bpm.mp3 100 175 20 -c:a libmp3lame -b:a 96k
generate beats/beat-120-bpm.mp3 120 220 20 -c:a libmp3lame -b:a 96k
generate beats/beat-140-bpm.mp3 140 165 20 -c:a libmp3lame -b:a 96k

# Samples: 8 seconds, mono WAV. The tests use the one at 95 BPM.
generate samples/sample-090-bpm.wav 90 262 8 -c:a pcm_s16le
generate samples/sample-095-bpm.wav 95 220 8 -c:a pcm_s16le
generate samples/sample-120-bpm.wav 120 196 8 -c:a pcm_s16le
