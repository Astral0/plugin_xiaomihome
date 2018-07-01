import sys
import socket
import struct
import argparse
import datetime
import utils
#inspired by gts66's first discovery
class XiaomiPacket():
	def __init__(self,token=''):
		self.magic =      "2131".decode('hex')
		self.length =     "0020".decode('hex')
		self.unknown1 =   "FFFFFFFF".decode('hex')
		self.devicetype = "FFFF".decode('hex')
		self.serial   =   "FFFF".decode('hex')
		self.stamp =      "FFFFFFFF".decode('hex')
		self.checksum =   "ffffffffffffffffffffffffffffffff".decode('hex')
		self.data = ""
		self.token = ""

	def setRaw(self, raw):
		self.magic =      raw[ 0: 2]
		self.length =     raw[ 2: 4]
		self.unknown1 =   raw[ 4: 8]
		self.devicetype = raw[ 8:10]
		self.serial =     raw[10:12]
		self.stamp =      raw[12:16]
		self.checksum =   raw[16:32]
		self.data =       raw[32:]
		if self.length=="0020".decode('hex'):
			self.token=self.checksum
		return

	def updateChecksum(self):
		self.checksum = utils.md5(self.magic+self.length+self.unknown1+self.devicetype+self.serial+self.stamp+self.token+self.data)
		return

	def getRaw(self):
		if len(self.data)>0:
			self.updateChecksum()
			raw = self.magic+self.length+self.unknown1+self.devicetype+self.serial+self.stamp+self.checksum+self.data
			return raw
		else:
			raw = self.magic+self.length+self.unknown1+self.devicetype+self.serial+self.stamp+self.checksum
			return raw

	def getPlainData(self):
		plain = utils.decrypt(self.token, self.data)
		return plain

	def setPlainData(self,plain):
		self.data = utils.encrypt(self.token, plain)
		length = len(self.data)+32
		self.length = format(length, '04x').decode('hex')
		self.updateChecksum()
		return

	def setHelo(self):
		self.magic =      "2131".decode('hex')
		self.length =     "0020".decode('hex')
		self.unknown1 =   "FFFFFFFF".decode('hex')
		self.devicetype = "FFFF".decode('hex')
		self.serial =     "FFFF".decode('hex')
		self.stamp =      "FFFFFFFF".decode('hex')
		self.checksum =   "ffffffffffffffffffffffffffffffff".decode('hex')
		self.data = ""
		self.token = ""
		return

	def printPacket(self,txt):
		txt=(txt[0:11]+"            ")[0:12]
		txt=txt+self.getRaw().encode('hex') 
		txt=txt[:160]
		logging.debug(txt)
		return

