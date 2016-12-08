import time

import silly

LAST_LOCATION_DEFAULT = "-1"
UNASSIGNED_NAME_DEFAULT = "Unassigned"


class Soldier:
    def __init__(self, personal_id,
                 job=None,
                 full_name=None,
                 last_seen_station_id=LAST_LOCATION_DEFAULT,
                 signal_strength=-1,
                 last_seen_time=-1):
        if not full_name:
            full_name = silly.name()
        if not job:
            job = silly.title()
        self.personal_id = personal_id  # can start with 0 - so not int
        self.job = job
        self.full_name = full_name
        self.last_seen_station_id = int(last_seen_station_id)
        self.signal_strength = signal_strength
        if last_seen_time < 0 and last_seen_station_id != LAST_LOCATION_DEFAULT:
            last_seen_time = time.time()
        self.last_seen_time = int(last_seen_time)
        self.signal_strength1 = 100
        self.signal_strength2 = 100

    def update_location(self, last_seen_station_id, signal_strength, last_seen_time=None):
        # signal_strength is absolute of a negative number(!), hence the order
        print("last_seen_station_id {} signal_strength {} self.signal_strength {}".format(last_seen_station_id,
                                                                                          signal_strength,
                                                                                          self.signal_strength
                                                                                          ))
        if not last_seen_time:
            last_seen_time = int(time.time())
        if int(last_seen_station_id) == 1:
            self.signal_strength1 = float(signal_strength)
        else:
            self.signal_strength2 = float(signal_strength)
        if self.signal_strength1 <= self.signal_strength2:
            self.last_seen_station_id = 1
        else:
            self.last_seen_station_id = 2
        self.signal_strength = signal_strength
        self.last_seen_time = float(last_seen_time)

    def __str__(self):
        return "{}".format(self.as_dict())

    def __repr__(self):
        return self.__str__()

    def as_dict(self):
        dict_repr = dict()
        dict_repr["personal_id"] = self.personal_id
        dict_repr["full_name"] = self.full_name
        dict_repr["job"] = self.job
        dict_repr["last_seen_station_id"] = self.last_seen_station_id
        dict_repr["signal_strength"] = self.signal_strength
        dict_repr["last_seen_time"] = self.last_seen_time
        return dict_repr
