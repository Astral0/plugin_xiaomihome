# This file is part of Jeedom.
#
# Jeedom is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Jeedom is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Jeedom. If not, see <http://www.gnu.org/licenses/>.

import subprocess
import os,re
import logging
import sys
import argparse
import time
import datetime
import signal
import json
import traceback
import globals
from devices.aquara import *
from devices.yeehome import *
from devices.xiaowifi import *
from threading import Timer
import thread
try:
	from jeedom.jeedom import *
except ImportError:
	print "Error: importing module from jeedom folder"
	sys.exit(1)

try:
	import queue
except ImportError:
	import Queue as queue

def push_data_from_aquara(model, sid, cmd,short_id,token, data, type,source):
	data_to_send = {}
	data_to_send['model'] = model
	data_to_send['sid'] = sid
	data_to_send['cmd'] = cmd
	data_to_send['short_id'] =short_id
	data_to_send['source'] =source
	data_to_send['token'] =token
	data_to_send['data'] = data
	globals.JEEDOM_COM.add_changes('devices::'+type+'_'+sid,data_to_send )

cb_aquara = lambda m, s, c, i, token, d, t ,src: push_data_from_aquara(m, s, c,i,token, d,t,src)

def listen():
	jeedom_socket.open()
	logging.info("Start listening...")
	thread.start_new_thread( read_socket, ('socket',))
	logging.debug('Read Socket Thread Launched')
	thread.start_new_thread( xiaomiconnector, ('aquara',))
	logging.debug('Aquara Thread Launched')

def read_socket(name):
	while 1:
		try:
			global JEEDOM_SOCKET_MESSAGE
			if not JEEDOM_SOCKET_MESSAGE.empty():
				logging.debug("Message received in socket JEEDOM_SOCKET_MESSAGE")
				message = json.loads(jeedom_utils.stripped(JEEDOM_SOCKET_MESSAGE.get()))
				if message['apikey'] != globals.apikey:
					logging.error("Invalid apikey from socket : " + str(message))
					return
				logging.debug('Received command from jeedom : '+str(message['cmd']))
				if message['cmd'] == 'send':
					logging.debug('Executing action on : '+str(message['model']))
					if message['type'] == 'aquara':
						devices.aquara.execute_action(message)
					elif message['type'] == 'yeelight':
						devices.yeehome.execute_action(message)
					elif message['type'] == 'wifi':
						devices.xiaowifi.execute_action(message)
				if message['cmd'] == 'read':
					logging.debug('Executing read on : '+str(message['model']))
					if message['type'] == 'aquara':
						devices.aquara.execute_action(message)
				if message['cmd'] == 'refresh':
					logging.debug('Refreshing : '+str(message['model']))
					if message['type'] == 'aquara':
						devices.aquara.refresh(message)
					if message['type'] == 'yeelight':
						devices.yeehome.refresh(message)
					elif message['type'] == 'wifi':
						devices.xiaowifi.refresh(message)
				if message['cmd'] == 'scanyeelight':
					logging.debug('Scanning yeelight')
					devices.yeehome.scan(2)
				if message['cmd'] == 'discover':
					logging.debug('Discovering : '+str(message['model']))
					devices.xiaowifi.discover(message)
		except Exception,e:
			logging.error("Exception on socket : %s" % str(e))
		time.sleep(0.3)

def xiaomiconnector(name) :
	globals.CONNECTOR = XiaomiConnector(data_callback=cb_aquara)
	while True:
		try:
			globals.CONNECTOR.check_incoming()
			time.sleep(0.05)
		except Exception as e:
			logging.error(str(e))

def handler(signum=None, frame=None):
	logging.debug("Signal %i caught, exiting..." % int(signum))
	shutdown()

def shutdown():
	logging.debug("Shutdown")
	logging.debug("Removing PID file " + str(globals.pidfile))
	try:
		os.remove(globals.pidfile)
	except:
		pass
	try:
		jeedom_socket.close()
	except:
		pass
	logging.debug("Exit 0")
	sys.stdout.flush()
	os._exit(0)

globals.log_level = "error"
globals.socketport = 55019
globals.sockethost = '127.0.0.1'
globals.pidfile = '/tmp/xiaomihomed.pid'
globals.apikey = ''
globals.callback = ''
globals.cycle = 0.05;

parser = argparse.ArgumentParser(description='Xiaomihomed Daemon for Jeedom plugin')
parser.add_argument("--device", help="Device", type=str)
parser.add_argument("--loglevel", help="Log Level for the daemon", type=str)
parser.add_argument("--pidfile", help="Value to write", type=str)
parser.add_argument("--callback", help="Value to write", type=str)
parser.add_argument("--apikey", help="Value to write", type=str)
parser.add_argument("--socketport", help="Socket Port", type=str)
parser.add_argument("--sockethost", help="Socket Host", type=str)
parser.add_argument("--cycle", help="Cycle to send event", type=str)
args = parser.parse_args()

if args.device:
	globals.device = args.device
if args.loglevel:
	globals.log_level = args.loglevel
if args.pidfile:
	globals.pidfile = args.pidfile
if args.callback:
	globals.callback = args.callback
if args.apikey:
	globals.apikey = args.apikey
if args.cycle:
	globals.cycle = float(args.cycle)
if args.socketport:
	globals.socketport = args.socketport
if args.sockethost:
	globals.sockethost = args.sockethost

globals.socketport = int(globals.socketport)
globals.cycle = float(globals.cycle)

jeedom_utils.set_log_level(globals.log_level)
logging.info('Start xiaomihomed')
logging.info('Log level : '+str(globals.log_level))
logging.info('Socket port : '+str(globals.socketport))
logging.info('Socket host : '+str(globals.sockethost))
logging.info('PID file : '+str(globals.pidfile))
logging.info('Apikey : '+str(globals.apikey))
logging.info('Callback : '+str(globals.callback))
logging.info('Cycle : '+str(globals.cycle))
import devices
signal.signal(signal.SIGINT, handler)
signal.signal(signal.SIGTERM, handler)
try:
	jeedom_utils.write_pid(str(globals.pidfile))
	globals.JEEDOM_COM = jeedom_com(apikey = globals.apikey,url = globals.callback,cycle=globals.cycle)
	if not globals.JEEDOM_COM.test():
		logging.error('Network communication issues. Please fix your Jeedom network configuration.')
		shutdown()
	jeedom_socket = jeedom_socket(port=globals.socketport,address=globals.sockethost)
	listen()
except Exception,e:
	logging.error('Fatal error : '+str(e))
	logging.debug(traceback.format_exc())
	shutdown()
