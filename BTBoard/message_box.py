import socket

AMIT_IP = "192.168.43.243"


def send_example(udp_ip=AMIT_IP, udp_port=8079, msg="Hello, World!"):
    print("ip:{} port {} msg{}".format(udp_ip, udp_port, msg))
    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)  # UDP
    msg_in_bytes = str.encode(msg)
    print("msg {} msg_in_bytes {}".format(msg, msg_in_bytes))
    sock.sendto(msg_in_bytes, (udp_ip, udp_port))
    print("Sent!!!")


def receive_example(udp_ip="192.168.43.77", udp_port=8079, demo=True):
    if demo:
        return '{station_id = 1, node_id = 1, power = 12, voltage = 2.33}', ('192.168.1.152', 54649)
    sock = socket.socket(socket.AF_INET,  # Internet
                         socket.SOCK_DGRAM)  # UDP
    sock.bind((udp_ip, udp_port))
    # sock.bind(("0.0.0.0", udp_port))  # could also use "0.0.0.0"
    # sock.setblocking(False)

    try:
        print("Listening at ip {} port {}".format(udp_ip, udp_port))
        data, addr = sock.recvfrom(1024)  # buffer size is 1024 bytes
        print("got it!")
        # print(data, addr)
        return data, addr
    except socket.error:
        print("No data")

    # data_str=data.decode('utf-8')
    return data, addr


receive_example(demo=False)
