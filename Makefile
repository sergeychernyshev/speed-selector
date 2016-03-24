.PHONY: all mask frames videos clean

TEST_FOLDER=results/${test}

FRAMES_FOLDER=${TEST_FOLDER}/frames
DIFFS_FOLDER=${TEST_FOLDER}/frames_diff
OVERLAY_FOLDER=${TEST_FOLDER}/frames_overlay
MASKED_FOLDER=${TEST_FOLDER}/frames_masked

FRAME_FILE=frame_0000.jpg
RESULT=${TEST_FOLDER}/result.xml
MASK=${TEST_FOLDER}/mask.png

all:
ifndef test
	$(error You must specify a test ID as 'test' parameter: make test=160301_7K_YF7)
else
	${MAKE} videos masked_metrics
endif

videos: ${TEST_FOLDER}/video.mp4 ${TEST_FOLDER}/diff_video.mp4 ${TEST_FOLDER}/diff_overlay_video.mp4

# Download video frames
${FRAMES_FOLDER}/${FRAME_FILE}:
	mkdir -p ${TEST_FOLDER}
	wget "http://www.webpagetest.org/video/downloadFrames.php?test=${test}&run=1&cached=0" -O ${TEST_FOLDER}/frames.zip
	mkdir -p ${FRAMES_FOLDER}
	unzip -d ${FRAMES_FOLDER} ${TEST_FOLDER}/frames.zip

# Download test results
${TEST_FOLDER}/result.xml:
	mkdir -p ${TEST_FOLDER}
	wget -q "http://www.webpagetest.org/xmlResult/${test}/" -O ${TEST_FOLDER}/result.xml

${MASK}: ${TEST_FOLDER}/result.xml ${FRAMES_FOLDER}/${FRAME_FILE}
	mkdir -p ${MASKED_FOLDER}
	php create_mask.php ${FRAMES_FOLDER}/${FRAME_FILE} ${RESULT} ${MASK}

FRAMES := $(wildcard ${FRAMES_FOLDER}/*.jpg)
MASKED_FRAMES := $(addprefix ${MASKED_FOLDER}/,$(notdir ${FRAMES}))

${MASKED_FOLDER}/%: ${FRAMES_FOLDER}/%
	composite ${MASK} $< $@

${TEST_FOLDER}/video.mp4: ${FRAMES_FOLDER}/${FRAME_FILE}
	php avs_to_mp4.php ${FRAMES_FOLDER}/video.avs ${FRAMES_FOLDER} ${TEST_FOLDER}/video.mp4

${TEST_FOLDER}/diff_video.mp4: ${TEST_FOLDER}/video.mp4
	mkdir -p ${DIFFS_FOLDER}
	php frame_diff.php ${FRAMES_FOLDER}/video.avs ${DIFFS_FOLDER}
	php avs_to_mp4.php ${FRAMES_FOLDER}/video.avs ${DIFFS_FOLDER} ${TEST_FOLDER}/diff_video.mp4

${TEST_FOLDER}/diff_overlay_video.mp4: ${TEST_FOLDER}/diff_video.mp4
	mkdir -p ${OVERLAY_FOLDER}
	php diff_overlay.php ${FRAMES_FOLDER}/video.avs ${DIFFS_FOLDER} ${OVERLAY_FOLDER}
	php avs_to_mp4.php ${FRAMES_FOLDER}/video.avs ${OVERLAY_FOLDER} ${TEST_FOLDER}/diff_overlay_video.mp4

masked_frames: ${MASK} ${MASKED_FRAMES}

${TEST_FOLDER}/masked.mp4: ${MASK} ${MASKED_FRAMES}
	php avs_to_mp4.php ${FRAMES_FOLDER}/video.avs ${MASKED_FOLDER} ${TEST_FOLDER}/masked.mp4

masked_metrics: ${TEST_FOLDER}/masked.mp4
	mkdir -p ${TEST_FOLDER}
	visualmetrics/visualmetrics.py -i ${TEST_FOLDER}/masked.mp4 -l

clean:
	rm -rf ${TEST_FOLDER}
