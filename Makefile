
TEST_FOLDER=results/${test}

FRAMES_FOLDER=${TEST_FOLDER}/frames
DIFFS_FOLDER=${TEST_FOLDER}/frames_diff
OVERLAY_FOLDER=${TEST_FOLDER}/frames_overlay
MASKED_FOLDER=${TEST_FOLDER}/frames_masked
MASKED_DIFF_FOLDER=${TEST_FOLDER}/frames_masked_diff
MASKED_OVERLAY_FOLDER=${TEST_FOLDER}/frames_masked_overlay
MASKED_OVER_ORIGINAL_FOLDER=${TEST_FOLDER}/frames_masked_over_original
MASKED_DIFF_OVER_ORIGINAL_FOLDER=${TEST_FOLDER}/frames_masked_diff_over_original

FRAME_FILE=frame_0000.jpg
AVS=${FRAMES_FOLDER}/video.avs
RESULT=${TEST_FOLDER}/result.xml
MASK=${TEST_FOLDER}/mask.png

all:
ifndef test
	$(error You must specify a test ID as 'test' parameter: make test=160301_7K_YF7)
else
	${MAKE} videos masked_metrics
endif

videos: ${TEST_FOLDER}/diff_over_original.mp4 ${TEST_FOLDER}/masked_diff_over_original.mp4 ${TEST_FOLDER}/masked_diff_over_masked.mp4 ${TEST_FOLDER}/masked_over_original.mp4

# Download video frames
${FRAMES_FOLDER}/${FRAME_FILE}:
	mkdir -p ${TEST_FOLDER}
	wget "http://www.webpagetest.org/video/downloadFrames.php?test=${test}&run=1&cached=0" -O ${TEST_FOLDER}/frames.zip
	mkdir -p ${FRAMES_FOLDER}
	unzip -d ${FRAMES_FOLDER} ${TEST_FOLDER}/frames.zip

# Generate refular videos (original, diff, and overlayed diff)
${TEST_FOLDER}/original.mp4: ${FRAMES_FOLDER}/${FRAME_FILE}
	php avs_to_mp4.php ${AVS} ${FRAMES_FOLDER} ${TEST_FOLDER}/original.mp4

${TEST_FOLDER}/diff.mp4: ${TEST_FOLDER}/original.mp4
	mkdir -p ${DIFFS_FOLDER}
	php frame_diff.php ${AVS} ${FRAMES_FOLDER} ${DIFFS_FOLDER}
	php avs_to_mp4.php ${AVS} ${DIFFS_FOLDER} ${TEST_FOLDER}/diff.mp4

${TEST_FOLDER}/diff_over_original.mp4: ${TEST_FOLDER}/diff.mp4
	mkdir -p ${OVERLAY_FOLDER}
	php diff_overlay.php ${AVS} ${FRAMES_FOLDER} ${DIFFS_FOLDER} ${OVERLAY_FOLDER}
	php avs_to_mp4.php ${AVS} ${OVERLAY_FOLDER} ${TEST_FOLDER}/diff_over_original.mp4

# Now generate the mask (masked original, diff, and overlayed diff)
${TEST_FOLDER}/result.xml:
	mkdir -p ${TEST_FOLDER}
	wget -q "http://www.webpagetest.org/xmlResult/${test}/" -O ${TEST_FOLDER}/result.xml

${MASK}: ${TEST_FOLDER}/result.xml ${FRAMES_FOLDER}/${FRAME_FILE}
	php create_mask.php ${FRAMES_FOLDER}/${FRAME_FILE} ${RESULT} ${MASK}

${TEST_FOLDER}/masked.mp4: ${MASK} ${TEST_FOLDER}/original.mp4
	mkdir -p ${MASKED_FOLDER}
	php apply_mask.php ${AVS} 1 ${MASK} ${FRAMES_FOLDER} ${MASKED_FOLDER}
	php avs_to_mp4.php ${AVS} ${MASKED_FOLDER} ${TEST_FOLDER}/masked.mp4

${TEST_FOLDER}/masked_diff.mp4: ${TEST_FOLDER}/masked.mp4
	mkdir -p ${MASKED_DIFF_FOLDER}
	php frame_diff.php ${AVS} ${MASKED_FOLDER} ${MASKED_DIFF_FOLDER}
	php avs_to_mp4.php ${AVS} ${MASKED_DIFF_FOLDER} ${TEST_FOLDER}/masked_diff.mp4

${TEST_FOLDER}/masked_diff_over_masked.mp4: ${TEST_FOLDER}/masked_diff.mp4
	mkdir -p ${MASKED_OVERLAY_FOLDER}
	php diff_overlay.php ${AVS} ${MASKED_FOLDER} ${MASKED_DIFF_FOLDER} ${MASKED_OVERLAY_FOLDER}
	php avs_to_mp4.php ${AVS} ${MASKED_OVERLAY_FOLDER} ${TEST_FOLDER}/masked_diff_over_masked.mp4

${TEST_FOLDER}/masked_over_original.mp4: ${TEST_FOLDER}/masked_diff.mp4
	mkdir -p ${MASKED_OVER_ORIGINAL_FOLDER}
	php apply_mask.php ${AVS} 0.7 ${MASK} ${FRAMES_FOLDER} ${MASKED_OVER_ORIGINAL_FOLDER}
	php avs_to_mp4.php ${AVS} ${MASKED_OVER_ORIGINAL_FOLDER} ${TEST_FOLDER}/masked_over_original.mp4

${TEST_FOLDER}/masked_diff_over_original.mp4: ${TEST_FOLDER}/masked_over_original.mp4
	mkdir -p ${MASKED_DIFF_OVER_ORIGINAL_FOLDER}
	php diff_overlay.php ${AVS} ${MASKED_OVER_ORIGINAL_FOLDER} ${MASKED_DIFF_FOLDER} ${MASKED_DIFF_OVER_ORIGINAL_FOLDER}
	php avs_to_mp4.php ${AVS} ${MASKED_DIFF_OVER_ORIGINAL_FOLDER} ${TEST_FOLDER}/masked_diff_over_original.mp4

masked_metrics: ${TEST_FOLDER}/masked.mp4
	visualmetrics/visualmetrics.py -i ${TEST_FOLDER}/masked.mp4

clean:
	rm -rf ${TEST_FOLDER}
