import json
import random

import time

import message_box
import soldier_module
import station_module

OUTPUT_FILE_TO_FE = "out/data.json"
DEMO_MODE = True
NUM_OF_STATIONS_TO_GENERATE = 2
NUM_OF_SOLDIERS_TO_GENERATE = 3
STATION_ID_TO_SEND_OUT = 5
LAST_SEEN_TIMEOUT = 100000000000

nodes_to_soldiers = dict()
stations = dict()


def get_next_personal_id():
    get_next_personal_id.val += 1
    return get_next_personal_id.val


get_next_personal_id.val = 499


def get_next_station_id():
    get_next_station_id.val += 1
    return get_next_station_id.val


get_next_station_id.val = 999


def get_next_node_id():
    get_next_node_id.val += 1
    return get_next_node_id.val


get_next_node_id.val = 0


def add_soldier(full_name=None, node_id=None, personal_id=None, last_seen_station_id=None, signal_strength=-1):
    if not node_id:
        node_id = get_next_node_id()
    if not personal_id:
        personal_id = get_next_personal_id()
    nodes_to_soldiers[node_id] = soldier_module.Soldier(full_name=full_name,
                                                        personal_id=personal_id,
                                                        last_seen_station_id=last_seen_station_id,
                                                        signal_strength=signal_strength)
    print("Added soldier node {} station {}".format(node_id, last_seen_station_id))


def add_station(station_id=None):
    if not station_id:
        station_id = get_next_station_id()
    stations[station_id] = station_module.Station(station_id)


def parse_data(data_str):
    """

    :param data_str: The format is: {station_id =<int>, node_id = <int>, power = <int>, voltage = <float>}' ('<ip>', 52095)
    :return:
    """
    # print("data_str {}".format(data_str))
    res_dict = dict()
    for attribute in data_str.split(","):
        key, value = attribute.split("=")
        key, value = key.strip("{} "), value.strip("{} ")
        # print("{}: {}".format(key, value))
        if isinstance(value, int):
            value = int(value)
        elif isinstance(value, float):
            value = float(value)
        res_dict[key] = value
    return res_dict


def handle_incoming_msg(data):
    try:
        data_str = data.decode('utf-8')
        print("data_str {}".format(data_str))
        parsed_data = parse_data(data.decode('utf-8'))
    except json.JSONDecodeError:
        print("Error while trying to parse data, raw bytestream: {}".format(data))
    station_id, node_id, signal_strength, voltage = parsed_data["station_id"], parsed_data["node_id"], parsed_data[
        "power"], parsed_data["voltage"]
    print("node_id {}".format(node_id))
    if station_id not in stations.keys():
        # print("Error!!! station num {} doesn't exist. Not handling msg...".format(station_id))
        print("station num {} didn't exist, adding it".format(station_id))
        add_station(station_id)
    new_data = dict()
    new_data[node_id] = signal_strength, voltage
    stations[station_id].update_nodes(new_data)

    # Currently creating mock soldier data. In the future this will be pre inserted
    if node_id not in nodes_to_soldiers:
        print("node num {} didn't exist, adding it".format(node_id))
        add_soldier(full_name="Soldier_{}".format(node_id), node_id=node_id, last_seen_station_id=station_id,
                    signal_strength=signal_strength)
    return parsed_data


def generate_data():
    for i in range(NUM_OF_SOLDIERS_TO_GENERATE):
        add_soldier()
    for i in range(NUM_OF_STATIONS_TO_GENERATE):
        add_station()
    for node_id in nodes_to_soldiers.keys():
        data = dict()
        station_id = random.choice(list(stations.keys()))
        data["station_id"] = station_id
        # print("node_id {} station_id {}".format(node_id, station_id))
        data["node_id"] = node_id
        data["power"] = random.randint(1, 20)
        # voltage is normalized to be in range [2.0, 5.0))
        data["voltage"] = (random.random() * 3) + 2
        handle_incoming_msg(data)


def db_presentor():
    with open('cur_db_state.{}'.format((time.time())), 'w') as out:
        out.write("nodes_to_soldiers:\n {}\nstations\n{}".format(nodes_to_soldiers, stations))


def update_info(parsed_data):
    print("parsed_data {}".format(parsed_data))
    soldier = nodes_to_soldiers[parsed_data["node_id"]]
    station_id = parsed_data["station_id"]
    soldier.update_location(station_id, signal_strength=parsed_data['power'])
    d = dict()
    # d["station_id"] = station_id
    # d["node_id"] = parsed_data["node_id"]
    # d["time"] = soldier.as_dict()["last_seen_time"]
    # d["true_location"] = soldier.as_dict()["last_seen_station_id"]
    # d_str = "data = '[" + str(json.dumps(d)) + "]';"
    d_str = "data = '["
    for node_id in nodes_to_soldiers:
        soldier = nodes_to_soldiers[node_id]
        print("cur node_id {}".format(node_id))
        # print("type(soldier) {}".format(type(soldier)))
        # if (int(time.time()) - soldier.as_dict()["last_seen_time"]) <= LAST_SEEN_TIMEOUT:
        d = dict()
        d["station_id"] = parsed_data["station_id"]
        d["node_id"] = node_id
        d["time"] = soldier.as_dict()["last_seen_time"]
        d["true_location"] = soldier.as_dict()["last_seen_station_id"]
        d_str += str(json.dumps(d))
        d_str += ","
        # else:
        #     print("Didn't put soldier {}".format(node_id))
    d_str = d_str[:len(d_str) - 1]
    d_str += "]';"
    # str = """
    #       data = '[\{station_id : {station_id}, "node_id" : "{node_id}", "time" : {last_seen_time}, "true_location" : "{last_seen_station_id}"\}]';
    # """.format(
    #     **parsed_data, **cur_soldier.as_dict())
    # out_filename = 'out/msg_from_station.{}'.format(time.time())
    # d_str = "data = '[" + str(json.dumps(d)) + "]';"
    with open(OUTPUT_FILE_TO_FE, 'w') as out:
        out.write(d_str)
    print("Wrote {} to {}".format(d, OUTPUT_FILE_TO_FE))


def main():
    # print('Hi! arg num: {} args: {}'.format(len(sys.argv), sys.argv))
    # if DEMO_MODE:
    #     generate_data()
    while True:
        data, addr = message_box.receive_example(demo=False)
        # try:
        parsed_data = handle_incoming_msg(data)
        print("parsed_data[station_id]: {}".format(parsed_data["station_id"]))
        update_info(parsed_data)
        if int(parsed_data["station_id"]) == STATION_ID_TO_SEND_OUT:
            print("Got {}".format(STATION_ID_TO_SEND_OUT))
            message_box.send_example()
        # except ValueError:
        #     print("Error while trying to handle incoming msg")

            # db_presentor()

            # with open('incoming_data.json', 'w') as data_to_ui:
            #     data_to_ui.write(data_str)


if __name__ == "__main__":
    # execute only if run as a script
    main()
