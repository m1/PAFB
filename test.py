#!/usr/bin/env python

import argparse
import subprocess
import sys
import json
from operator import itemgetter
from collections import OrderedDict
import random

FRAMEWORKS = ['lumen', 'phalcon', 'silex', 'slim']
random.shuffle(FRAMEWORKS)

KEYS = {
	'Time taken for tests': 'time_taken',
	'Complete requests': 'complete',
	'Failed requests': 'failed',
	'Requests per second': 'rps',
	'Time per request': 'tpr'
}

SCORES = ['1st', '2nd', '3rd', '4th']

def getdata(req):
	data = {}

	for line in req.splitlines():
		line = [x.strip() for x in line.split(':', 2)]

		if line[0] in KEYS.keys():
			key = KEYS[line[0]]
			if key == 'tpr':
				s = float(line[1].split('[ms]', 2)[0].strip())
				
				if 'tpr' in data:
					data['tpr_concurrent'] = s
				else:
					data['tpr'] = s
			elif key == 'rps':
				data[key] = float(line[1].split('[#/sec]', 2)[0].strip())
			elif key == 'time_taken':
				data[key] = float(line[1].split(' ', 2)[0].strip())
			elif key == 'complete' or key == 'failed':
				data[key] = int(line[1])

	return data


parser = argparse.ArgumentParser(description='PAFB install')

choices = ['all']
choices.extend(FRAMEWORKS)

parser.add_argument("-f", "--frameworks", 
                    help="Which frameworks to test", 
                    choices=choices, 
                    nargs='+',
                    default='all')

parser.add_argument("-n", "--number", 
                    help="Number of requests to make", 
                    type=int,
                    default=1000)

parser.add_argument("-c", "--concurrent", 
                    help="Number of multiple requests to make at a time", 
                    type=int,
                    default=100)
args = parser.parse_args()

if args.frameworks == 'all' or 'all' in args.frameworks:
	args.frameworks = FRAMEWORKS

try:
    subprocess.check_output("which ab", shell=True)
except subprocess.CalledProcessError:
	sys.exit('Install ApacheBench (https://httpd.apache.org/docs/2.4/programs/ab.html) then run this tester again')

print 'Welcome to the PHP API Framework benchmark (PAFB)'
print ''

tests = OrderedDict()
tests['insert'] = 'ab -p tests/insert -n {} -c {} http://{}.pafb.dev:80/'
tests['update'] = 'ab -p tests/update -n {} -c {} http://{}.pafb.dev:80/aaa'
tests['select'] = 'ab -n {} -c {} http://{}.pafb.dev:80/bbb'
tests['delete'] = 'ab -p tests/insert -n {} -c {} http://{}.pafb.dev:80/delete'
tests['index'] = 'ab  -n {} -c {} http://{}.pafb.dev:80/'

results = {}
winners = {framework: {'score': 0, 'total_rps': 0, 'name': framework} for framework in FRAMEWORKS}

for test_key, test_value in tests.iteritems():
	results[test_key] = []

	print 'Testing: {}'.format(test_key.title())

	for framework in FRAMEWORKS:
		req = test_value.format(args.number, args.concurrent, framework)
		test = subprocess.check_output(req, shell=True, stderr=subprocess.STDOUT)
		
		data = getdata(test)
		data.update({'name': framework})

		if data['failed'] > 0:
			print 'Test failed for {}'.format(framework)
			data['rps'] = 0

		results[test_key].append(data)

	results[test_key] = sorted(results[test_key], key=itemgetter('rps'), reverse=True)

	print "\nResults:"

	for index, result in enumerate(results[test_key]):
		print '{}. {} - {} requests per second'.format(SCORES[index], result['name'].title(), result['rps'])

	print ''

framework_len = len(FRAMEWORKS)

for result in results.values():
	for index, framework in enumerate(result):
		winners[framework['name']]['score'] += framework_len - index
		winners[framework['name']]['total_rps'] = round(winners[framework['name']]['total_rps'] + framework['rps'], 2)

winners = sorted(winners.values(), key=itemgetter('score', 'total_rps'), reverse=True)

print "Overall Results"

for index, winner in enumerate(winners):
	print '{}. {} - {} total rps'.format(SCORES[index], winner['name'].title(), winner['total_rps'])
