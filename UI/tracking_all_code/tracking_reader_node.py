#######################################################
# Active RFID People / Asset Tracking System
# http://www.ns-tech.co.uk/active-rfid-tracking-system/
#######################################################

#Replace with the address of the reader plugged into a PC
PC_LINKED_READER = '\x03\xa2\x83'

#Receive pings from tags
def tagping(address):

    #Read signal
    lq = getLq()

    #Send tag id, signal, reader id to reader connected to server (via mesh network)
    rpc(PC_LINKED_READER, 'tagping_collect', address, lq, localAddr())
