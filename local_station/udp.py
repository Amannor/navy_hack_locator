import socket

sock = socket.socket(socket.AF_INET,  # Internet
                         socket.SOCK_DGRAM)  # UDP
def udpConnect():
    sock = socket.socket(socket.AF_INET,  # Internet
                         socket.SOCK_DGRAM)  # UDP


def sendUdp(ip, station_id, node_id):


    json = "{station_id =" + str(station_id) + ", node_id = " + str(node_id) + ", power = 12, voltage = 2.33}"

    UDP_IP = ip
    UDP_PORT = 8079
    MESSAGE = json

    print("UDP target IP:", UDP_IP)
    print("UDP target port:", UDP_PORT)
    print("message:", MESSAGE)

    sock.sendto(str.encode(MESSAGE, 'utf-8'), (UDP_IP, UDP_PORT))
