package kr.devx.catcher;

import android.app.Activity;
import android.content.ContentValues;
import android.os.AsyncTask;
import org.json.JSONException;
import org.json.JSONObject;

public class Catcher {
    public static void newLogGUI(Activity activity, String key, double marketMoveMinRate, String marketPackage) {

    }

    public static void newLog(String key, Log log) {
        ContentValues values = new ContentValues();
        values.put("service_key", key);
        values.put("log_user", log.getUser());
        values.put("log_tag", log.getTag());
        values.put("log_level", log.getLevel());
        values.put("log_title", log.getTitle());
        values.put("log_content", log.getContent());
        NetworkTask netTask = new NetworkTask("https://api.devx.kr/Catcher/v1/log_new.php", values);
        netTask.execute();
    }

    static class NetworkTask extends AsyncTask<Void, Void, String> {
        private String url;
        private ContentValues values;

        NetworkTask(String url, ContentValues values) {
            this.url = url;
            this.values = values;
        }

        @Override
        protected String doInBackground(Void... params) {

            String result;
            RequestHttpURLConnection requestHttpURLConnection = new RequestHttpURLConnection();
            result = requestHttpURLConnection.request(url, values);

            return result;
        }

        @Override
        protected void onPostExecute(String s) {
            super.onPostExecute(s);
            try {
                JSONObject result = new JSONObject(s);
                int resultCode = result.getInt("result");
                if (resultCode == 0) {
                    android.util.Log.i("DEVX-CATCHER","SUCCESS : LOG ADDED : " + result.getInt("log_index"));
                } else {
                    if (resultCode == -1) {
                        android.util.Log.e("DEVX-CATCHER","FAIL : " + result.getInt("error"));
                        android.util.Log.e("DEVX-CATCHER","FAIL : " + result.getInt("error_debug"));
                    }
                    if (resultCode == -2) {
                        android.util.Log.e("DEVX-CATCHER","FAIL : " + result.getInt("error"));
                        android.util.Log.e("DEVX-CATCHER","FAIL : " + result.getInt("error_debug"));
                    }
                    if (resultCode == -3) {
                        android.util.Log.e("DEVX-CATCHER","FAIL : " + result.getInt("error"));
                        android.util.Log.e("DEVX-CATCHER","FAIL : " + result.getInt("error_debug"));
                    }
                }
            } catch (JSONException e) {
                e.printStackTrace();
                android.util.Log.e("DEVX-CATCHER","ERROR : " + s);
            }
        }
    }
}
