.PHONY: all mask frames clean

TEST_FOLDER=results/${test}

FRAMES_FOLDER=${TEST_FOLDER}/frames
MASKED_FOLDER=${TEST_FOLDER}/frames_masked

FRAME_FILE=frame_0000.jpg
RESULT=${TEST_FOLDER}/result.xml
MASK=${TEST_FOLDER}/mask.png

all:
ifndef test
	$(error You must specify a test ID as 'test' parameter: make test=160301_7K_YF7)
else
	${MAKE} masked_frames
endif

# Create test folder
${TEST_FOLDER}:
	mkdir -p ${TEST_FOLDER}

# Download video frames
${FRAMES_FOLDER}/${FRAME_FILE}: ${TEST_FOLDER}
		wget "http://www.webpagetest.org/video/downloadFrames.php?test=${test}&run=1&cached=0" -O ${TEST_FOLDER}/frames.zip
		mkdir -p ${FRAMES_FOLDER}
		unzip -d ${FRAMES_FOLDER} ${TEST_FOLDER}/frames.zip

# Download test results
${TEST_FOLDER}/result.xml: ${TEST_FOLDER}
	wget -q "http://www.webpagetest.org/xmlResult/${test}/" -O ${TEST_FOLDER}/result.xml

${MASK}: ${TEST_FOLDER}/result.xml ${FRAMES_FOLDER}/${FRAME_FILE}
	php create_mask.php ${FRAMES_FOLDER}/${FRAME_FILE} ${RESULT} ${MASK}

FRAMES := $(wildcard ${FRAMES_FOLDER}/*.jpg)
MASKED_FRAMES := $(addprefix ${MASKED_FOLDER}/,$(notdir ${FRAMES}))

${MASKED_FOLDER}/%: ${FRAMES_FOLDER}/%
	mkdir -p ${MASKED_FOLDER}
	composite ${MASK} $< $@

masked_frames: ${MASK} ${MASKED_FRAMES}

#${TEST_FOLDER}/masked.mp4: ${MASK} ${MASKED_FRAMES}
#	ffmpeg -f image2 -framerate 20 -pattern_type glob -i "${MASKED_FOLDER}/frame_*.jpg" -vcodec libx264 -pix_fmt yuv420p ${TEST_FOLDER}/masked.mp4

#masked_metrics: ${TEST_FOLDER} ${TEST_FOLDER}/masked.mp4
#	visualmetrics/visualmetrics.py -i ${TEST_FOLDER}/masked.mp4 -l

clean:
	rm -rf ${TEST_FOLDER}
