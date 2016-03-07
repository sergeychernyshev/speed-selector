.PHONY: all mask frames clean

TEST_FOLDER=results/${test}

FRAMES_FOLDER=${TEST_FOLDER}/frames
MASKED_FOLDER=${TEST_FOLDER}/frames_masked

REFERENCE=${TEST_FOLDER}/1_screen.jpg
FRAME_FILE=ms_000000.png
XMLRESULT=${TEST_FOLDER}/result.xml
MASK=${TEST_FOLDER}/mask.png

all:
ifndef test
	$(error You must specify a test ID as 'test' parameter: make test=160301_7K_YF7)
else
	${MAKE} masked_metrics
endif

# Create test folder
${TEST_FOLDER}:
	mkdir -p ${TEST_FOLDER}

# Download the video and create frames files
${TEST_FOLDER}/video.mp4:
	wget -q "http://www.webpagetest.org/video/download.php?id=${test}.1.0" -O ${TEST_FOLDER}/video.mp4
	visualmetrics/visualmetrics.py -i ${TEST_FOLDER}/video.mp4 -l -d ${FRAMES_FOLDER}

# Download test results
${TEST_FOLDER}/result.xml:
	wget -q "http://www.webpagetest.org/xmlResult/${test}/" -O ${TEST_FOLDER}/result.xml

# Download reference image
${REFERENCE}:
	wget -q "http://www.webpagetest.org/getfile.php?test=${test}&file=1_screen.jpg" -O ${REFERENCE}

${MASK}: ${TEST_FOLDER}/result.xml ${REFERENCE} ${TEST_FOLDER}/video.mp4
	php create_mask.php ${REFERENCE} ${FRAMES_FOLDER}/${FRAME_FILE} ${XMLRESULT} ${MASK}

FRAMES := $(wildcard ${FRAMES_FOLDER}/*)
MASKED_FRAMES := $(addprefix ${MASKED_FOLDER}/,$(notdir ${FRAMES}))

${MASKED_FOLDER}/%: ${FRAMES_FOLDER}/%
	mkdir -p ${MASKED_FOLDER}
	php composite_and_crop.php ${REFERENCE} $< ${MASK} $@

${TEST_FOLDER}/masked.mp4: ${MASK} ${MASKED_FRAMES}
	ffmpeg -f image2 -framerate 10 -pattern_type glob -i "${MASKED_FOLDER}/ms_*.png" -vcodec libx264 -pix_fmt yuv420p ${TEST_FOLDER}/masked.mp4

masked_metrics: ${TEST_FOLDER} ${TEST_FOLDER}/masked.mp4
	visualmetrics/visualmetrics.py -i ${TEST_FOLDER}/masked.mp4 -l -d ${MASKED_FOLDER}

clean:
	rm -rf ${TEST_FOLDER}
