.PHONY: all mask frame clean

FRAME=001100

TEST_FOLDER=results/${test}

FRAMES_FOLDER=${TEST_FOLDER}/frames
MASKED_FOLDER=${TEST_FOLDER}/frames_masked

REFERENCE=${TEST_FOLDER}/1_screen.jpg
FRAME_FILE=ms_${FRAME}.png
XMLRESULT=${TEST_FOLDER}/result.xml
MASK=${TEST_FOLDER}/mask.png

all:
ifndef	test
	$(error You must specify a test ID as 'test' parameter: make test=160301_7K_YF7)
else
	${MAKE} ${TEST_FOLDER} mask frame
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

mask: ${TEST_FOLDER}/result.xml ${REFERENCE} ${TEST_FOLDER}/video.mp4
	php create_mask.php ${REFERENCE} ${FRAMES_FOLDER}/${FRAME_FILE} ${XMLRESULT} ${MASK}

frame:
	mkdir -p ${MASKED_FOLDER}
	composite ${MASK} ${FRAMES_FOLDER}/${FRAME_FILE} ${MASKED_FOLDER}/${FRAME_FILE}

clean:
	rm -rf ${FRAMES_FOLDER} ${MASK} ${MASKED_FOLDER}
