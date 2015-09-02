<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 zhgzhg
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

// Simple Demonstration how use GPhpThread

require_once 'GPhpThread.php';

class MyThread extends GPhpThread {
	public function run() {
		echo 'Hello, I am a thread with id ' . getmypid() . "!\nTrying to lock the critical section\n";
		if ($this->criticalSection->lock()) {
			echo "=--- locked " . getmypid() . "\n";
			$this->criticalSection->addOrUpdateResource('IAM', getmypid());
			$this->criticalSection->addOrUpdateResource('IAMNOT', '0xdead1');
			$this->criticalSection->removeResource('IAMNOT');
			$this->criticalSection->unlock();
			echo "=--- unlocked " . getmypid() . "\n";
		}
	}
}

echo "Master main EP " . getmypid() . "\n";

$criticalSection = new GPhpThreadCriticalSection();

$threadPool = array();
$tpSize = 20;

for ($i = 1; $i <= $tpSize; ++$i) {
	$threadPool[$i] = new MyThread($criticalSection);
}

GPhpThread::BGN_HIGH_PRIOR_EXEC_BLOCK(); // this will result fast thread creation but, because of that all threads will try
// to reach the critical section at once so it will take twice the time for each one in order to lock the critical section

for ($i = 1; $i <= $tpSize; ++$i) {
	$threadPool[$i]->start();
	echo "{$i} of {$tpSize} started\n";
}

GPhpThread::END_HIGH_PRIOR_EXEC_BLOCK();

for ($i = 1; $i <= $tpSize; ++$i) {
	$threadPool[$i]->join();
}

echo "\n\n---The last writing in the critical section was done by thread---\n";
echo "---" . $criticalSection->getResourceValueFast('IAM') . "\n\n";
?>
