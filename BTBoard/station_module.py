POWER_INDEX = 0
VOLTAGE_INDEX = 1


# example incoming udp msg: station_id = 1, node_id = 1, power = 12, voltage = 2.33


class Station:
    def __init__(self, station_id):
        """
        :param station_id:
        """
        self.station_id = station_id
        self.nodes = dict()  # nodes: will be dict: {node_id : (power_value, voltage_value)}

    def backup_changes(self, robust_log=False):
        print("Backing up station no. {} with {} nodes".format(self.station_id, len(self.nodes)))
        if robust_log:
            print("Nodes: {}".format(self.nodes))

    def update_nodes(self, new_nodes):
        self.backup_changes()  # TODO - send only the keys that are relevant to update
        for new_node in new_nodes:
            self.nodes[new_node] = new_nodes[new_node]

    def __str__(self):
        return "Station num {} nodes {}".format(self.station_id, self.nodes)

    def __repr__(self):
        return self.__str__()

    def as_dict(self):
        dict_repr = dict()
        dict_repr["station_id"] = self.station_id
        dict_repr["nodes"] = self.nodes
        return dict_repr
