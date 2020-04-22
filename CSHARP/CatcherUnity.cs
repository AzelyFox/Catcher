using System;
using System.IO;
using System.Net;
using System.Text;
using Newtonsoft.Json.Linq;
using UnityEngine;

namespace CatcherUnity
{
    public class Catcher
    {

        public static void newLog(string key, Log log)
        {
            networkAsync(key, log);
        }
        
        private static async void networkAsync(string key, Log log)
        {
            try
            {
                UTF8Encoding encoding = new UTF8Encoding();

                string postData = "service_key=" + key;
                postData += "&log_user=" + log.getUser(); 
                postData += "&log_tag=" + log.getTag(); 
                postData += "&log_level=" + log.getLevel(); 
                postData += "&log_title=" + log.getTitle(); 
                postData += "&log_content=" + log.getContent(); 

                byte[] data = encoding.GetBytes(postData);

                WebRequest request = WebRequest.Create("https://api.devx.kr/Catcher/v1/log_new.php");
                request.Method = "POST";
                request.ContentType = "application/x-www-form-urlencoded";
                request.Headers["ContentLength"] = data.Length.ToString();

                Stream stream = await request.GetRequestStreamAsync();
                stream.Write(data, 0, data.Length);

                WebResponse response = await request.GetResponseAsync();

                stream = response.GetResponseStream();

                if (stream == null)
                {
                    try
                    {
                        Debug.Log("CATCHER FAIL : response null");
                    }
                    catch (Exception e)
                    {
                        
                    }
                    return;
                }
                
                StreamReader ar = new StreamReader(stream);

                JObject resultObject = JObject.Parse(ar.ReadToEnd());
                
                if (resultObject.GetValue("result").Value<int>() == 0)
                {
                    try
                    {
                        Debug.Log("CATCHER SUCCESS : " + resultObject.GetValue("log_index").Value<string>());
                    }
                    catch (Exception e)
                    {
                        
                    } 
                }
                else
                {
                    Debug.Log("CATCHER FAIL : " + resultObject.GetValue("error").Value<string>());
                    Debug.Log("CATCHER FAIL : " + resultObject.GetValue("error_debug").Value<string>());
                }
            }
            catch (Exception e)
            {
                try
                {
                    Debug.Log("CATCHER FAIL : " + e);
                }
                catch (Exception)
                {
                }
            }
            
        }
        
    }
    
    public class Log {
        private readonly string TAG;
        private readonly string USER;
        private readonly int LEVEL;
        private readonly string TITLE;
        private readonly string CONTENT;

        public class Builder {
            public readonly string USER;
            public readonly int LEVEL;
            public string TAG = "";
            public string TITLE = "";
            public string CONTENT = "";

            public Builder(string _user, int _level) {
                this.USER = _user.Replace("&", " ").Replace("=", " ");
                this.LEVEL = _level;
            }

            public Builder tag(string _tag) {
                this.TAG = _tag.Replace("&", " ").Replace("=", " ");
                return this;
            }

            public Builder title(string _title) {
                this.TITLE = _title.Replace("&", " ").Replace("=", " ");
                return this;
            }

            public Builder content(string _content) {
                this.CONTENT = _content.Replace("&", " ").Replace("=", " ");
                return this;
            }

            public Log build() {
                return new Log(this);
            }
        }

        private Log(Builder builder) {
            TAG = builder.TAG;
            USER = builder.USER;
            LEVEL = builder.LEVEL;
            TITLE = builder.TITLE;
            CONTENT = builder.CONTENT;
        }

        public string getTag() {
            return TAG;
        }

        public string getUser() {
            return USER;
        }

        public int getLevel() {
            return LEVEL;
        }

        public string getTitle() {
            return TITLE;
        }

        public string getContent() {
            return CONTENT;
        }
    }
}