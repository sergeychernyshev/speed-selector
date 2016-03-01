.PHONY: all mask frame clean

TEST_ID=160301_XY_SMR
FRAME=001400

TEST_FOLDER=results/${TEST_ID}

FRAMES_FOLDER=${TEST_FOLDER}/frames/
MASKED_FOLDER=${TEST_FOLDER}/frames_masked/

REFERENCE=${TEST_FOLDER}/1_screen.jpg
FRAME_FILE=ms_${FRAME}.png
XMLRESULT=${TEST_FOLDER}/result.xml
MASK=${TEST_FOLDER}/mask.png

all: mask frame

mask:
	php create_mask.php ${REFERENCE} ${FRAMES_FOLDER}/${FRAME_FILE} ${XMLRESULT} ${MASK}

frame:
	mkdir -p ${MASKED_FOLDER}
	composite ${MASK} ${FRAMES_FOLDER}/${FRAME_FILE} ${MASKED_FOLDER}/${FRAME_FILE}

clean:
	rm -rf ${MASK} ${MASKED_FOLDER}
