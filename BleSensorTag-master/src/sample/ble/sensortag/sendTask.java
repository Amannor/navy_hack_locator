package sample.ble.sensortag;

import android.os.AsyncTask;
import android.util.Log;

import java.net.DatagramPacket;
import java.net.DatagramSocket;
import java.net.InetAddress;

/**
 * Created by lenovo on 01-Dec-16.
 */


public class sendTask extends AsyncTask<String, Integer, Integer> {

    private Exception exception;

    protected Integer doInBackground(String... params) {
        try {
            DatagramSocket s = new DatagramSocket();
            InetAddress local = InetAddress.getByName(params[0]);
            int msg_length = params[2].length();
            byte[] message = params[2].getBytes();
            DatagramPacket p = new DatagramPacket(message, msg_length, local, Integer.parseInt(params[1]));
            s.send(p);
        } catch (Exception e) {
            Log.e("udp","send",e);
        }
        return 0;
    }

}
