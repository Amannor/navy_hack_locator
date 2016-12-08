#######################################################
# Active RFID People / Asset Tracking System
# http://www.ns-tech.co.uk/active-rfid-tracking-system/
#######################################################

from synapse.nvparams import *
from synapse.switchboard import *

def startup():
    global seccounter

    #Set transmit power level
    txPwr(17)

    #Disable UART 0
    initUart(0, 0)
    flowControl(0, False)

    #Disable UART 1
    initUart(1, 0)
    flowControl(1, False)

    #Set all GPIOs to inputs with pullup to cut down on current use
    setPinDir(0, 0)
    setPinPullup(0, 1)

    setPinDir(1, 0)
    setPinPullup(1, 1)

    setPinDir(2, 0)
    setPinPullup(2, 1)

    setPinDir(3, 0)
    setPinPullup(3, 1)

    setPinDir(4, 0)
    setPinPullup(4, 1)

    setPinDir(5, 0)
    setPinPullup(5, 1)

    setPinDir(6, 0)
    setPinPullup(6, 1)

    setPinDir(7, 0)
    setPinPullup(7, 1)

    crossConnect(DS_NULL, DS_PACKET_SERIAL)

    setPinDir(8, 0)
    setPinPullup(8, 1)

    setPinDir(9, 0)
    setPinPullup(9, 1)

    setPinDir(10, 0)
    setPinPullup(10, 1)

    setPinDir(11, 0)
    setPinPullup(11, 1)

    setPinDir(12, 0)
    setPinPullup(12, 1)

    setPinDir(13, 0)
    setPinPullup(13, 1)

    setPinDir(14, 0)
    setPinPullup(14, 1)

    setPinDir(15, 0)
    setPinPullup(15, 1)

    setPinDir(16, 0)
    setPinPullup(16, 1)

    setPinDir(17, 0)
    setPinPullup(17, 1)

    setPinDir(18, 0)
    setPinPullup(18, 1)

    #Turn off node relaying packets for others
    saveNvParam(NV_MESH_OVERRIDE_ID, 1)

def poll100ms():

    #print "Sending Ping"
    mcastRpc(1, 1, "tagping", localAddr())

    #Go back to sleep
    sleep(0, 3)


#Install event handlers
snappyGen.setHook(SnapConstants.HOOK_STARTUP, startup)
snappyGen.setHook(SnapConstants.HOOK_100MS, poll100ms)
