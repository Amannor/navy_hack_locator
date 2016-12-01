import time
import serial
import udp

# configure the serial connections (the parameters differs on the device you are connecting to)
try:
    ser = serial.Serial(
        port='COM5',
        baudrate=115200,
        parity=serial.PARITY_ODD,
        stopbits=serial.STOPBITS_ONE,
        bytesize=serial.EIGHTBITS
    )
    ser.isOpen()
except Exception:
    print("Serial connetction failed")
ip = "192.168.1.144"
STATION_ID = 1000
udp.udpConnect()

sendi = b'\x03'
while 1:
    # get keyboard input
    # Python 3 users
    # send the character to the device
    # (note that I happend a \r\n carriage return and line feed to the characters - this is requested by my device)
    line=""
    try:
        ret = udp.recvUdp()
        if 0 != ret[1]:
            ret = ser.write(sendi)
    except Exception:
        print ("No incoming message")
    try:
        line = str(ser.readline(), 'utf-8')
        print("wrote " + str(ret) + " bytes")
    except serial.serialutil.SerialException:
        print("Error reading serial")
        time.sleep(1)
    except Exception:
        print("Serial connetction failed")
    nums = line.split()
    if len(nums) > 0:
        udp.sendUdp(ip, STATION_ID, str(nums[0]))
        print("The number is: ", nums[0])
    else:
        udp.sendUdp(ip, STATION_ID, 5)
        print("The number is: ", 5)
        time.sleep(1)
