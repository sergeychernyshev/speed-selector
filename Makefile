.PHONY: all mask frames clean

TEST_FOLDER=results/${test}

FRAMES_FOLDER=${TEST_FOLDER}/frames
DIFFS_FOLDER=${TEST_FOLDER}/frames_diff
MASKED_FOLDER=${TEST_FOLDER}/frames_masked

FRAME_FILE=frame_0000.jpg
RESULT=${TEST_FOLDER}/result.xml
MASK=${TEST_FOLDER}/mask.png

all:
ifndef test
	$(error You must specify a test ID as 'test' parameter: make test=160301_7K_YF7)
else
	# ${MAKE} masked_frames
	${MAKE} ${TEST_FOLDER}/video.mp4 ${TEST_FOLDER}/diff_video.mp4
endif

# Download video frames
${FRAMES_FOLDER}/video.avs:
	mkdir -p ${TEST_FOLDER}
	wget "http://www.webpagetest.org/video/downloadFrames.php?test=${test}&run=1&cached=0" -O ${TEST_FOLDER}/frames.zip
	mkdir -p ${FRAMES_FOLDER}
	unzip -d ${FRAMES_FOLDER} ${TEST_FOLDER}/frames.zip

${FRAMES_FOLDER}/${FRAME_FILE}: ${FRAMES_FOLDER}/video.avs

# Download test results
${TEST_FOLDER}/result.xml: ${TEST_FOLDER}
	mkdir -p ${TEST_FOLDER}
	wget -q "http://www.webpagetest.org/xmlResult/${test}/" -O ${TEST_FOLDER}/result.xml

${MASK}: ${TEST_FOLDER}/result.xml ${FRAMES_FOLDER}/${FRAME_FILE}
	php create_mask.php ${FRAMES_FOLDER}/${FRAME_FILE} ${RESULT} ${MASK}

FRAMES := $(wildcard ${FRAMES_FOLDER}/*.jpg)
MASKED_FRAMES := $(addprefix ${MASKED_FOLDER}/,$(notdir ${FRAMES}))

${MASKED_FOLDER}/%: ${FRAMES_FOLDER}/%
	mkdir -p ${MASKED_FOLDER}
	composite ${MASK} $< $@

${TEST_FOLDER}/video.mp4: ${FRAMES_FOLDER}/video.avs
	php avs_to_mp4.php ${FRAMES_FOLDER}/video.avs ${FRAMES_FOLDER} ${TEST_FOLDER}/video.mp4

${TEST_FOLDER}/diff_video.mp4: ${FRAMES_FOLDER}/video.avs
	mkdir -p ${DIFFS_FOLDER}
	php frame_diff.php ${FRAMES_FOLDER}/video.avs ${DIFFS_FOLDER}
	php avs_to_mp4.php ${FRAMES_FOLDER}/video.avs ${DIFFS_FOLDER} ${TEST_FOLDER}/diff_video.mp4

masked_frames: ${MASK} ${MASKED_FRAMES}

#${TEST_FOLDER}/masked.mp4: ${MASK} ${MASKED_FRAMES}
#	ffmpeg -f image2 -framerate 20 -pattern_type glob -i "${MASKED_FOLDER}/frame_*.jpg" -vcodec libx264 -pix_fmt yuv420p ${TEST_FOLDER}/masked.mp4

#masked_metrics: ${TEST_FOLDER}/masked.mp4
#	mkdir -p ${TEST_FOLDER}
#	visualmetrics/visualmetrics.py -i ${TEST_FOLDER}/masked.mp4 -l

clean:
	rm -rf ${TEST_FOLDER}
