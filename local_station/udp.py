import socket

sock = socket.socket(socket.AF_INET,  # Internet
                         socket.SOCK_DGRAM)  # UDP
sock.setblocking(False)
def udpConnect():
    sock = socket.socket(socket.AF_INET,  # Internet
                         socket.SOCK_DGRAM)  # UDP

def sendUdp(ip, station_id, node_id):
    json = "{station_id =" + str(station_id) + ", node_id = " + str(node_id) + ", power = 12, voltage = 2.33}"
    UDP_PORT = 8079
    sock.sendto(str.encode(json, 'utf-8'), (ip, UDP_PORT))


def recvUdp():
    try:
        data, addr = sock.recvfrom(1024)  # buffer size is 1024 bytes
        print("received message:", data)
        return data, addr
    except Exception:
        return 0, 0
