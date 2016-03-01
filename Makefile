TEST_ID=160301_XY_SMR
FRAME=001400

all: mask frame

mask:
	php create_mask.php results/${TEST_ID}/result.xml results/${TEST_ID}/mask.png
frame:
	mkdir -p results/${TEST_ID}/frames_masked/
	composite results/${TEST_ID}/mask.png results/${TEST_ID}/frames/ms_${FRAME}.png results/${TEST_ID}/frames_masked/ms_${FRAME}.png
