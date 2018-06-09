import requests
import json
import base64


class RedCap(object):
    """
    The purpose of this class is to facilitate the interaction between flask and REDCap.
    self.url and self.token variables are generated from the config.json file.
    Configure those to your specifications.
    """
    def __init__(self):
        
        self.url = json.load(open("config.json"))["redcap_url"]
        self.token = json.load(open("config.json"))["redcap_url"]


    ## this will find the largest record id and add one
    ## FindMyVariant had a specific record id scheme, this will need to be tweeked.
    def generate_next_record_id(self):
        data = {
            'token':self.token,
            'content':'record',
            'format':'json',
            'type':'flat',
            ## This field will depend on what record types are in your projectd
            'fields[0]':'proband_id',
            'rawOrLabel':'raw',
            'rawOrLabelHeaders':'raw',
            'exportCheckboxLabel':'false',
            'exportSurveyFields':'false',
            'exportDataAccessGroups':'false',
            'returnFormat':'json'
        }

        ## this is getting all the record ids that fall under the 'proband_id' category
        r = requests.post(self.url, data=data)

        output = json.loads(r.text)
        ids = []

        ## this will have to be tweeked to fit your study schema
        ## the point of this function is to return the next highest record id
        ## example: highest id = NIH VUS 102, function returns NIH VUS 103
        for i in output:
            if i["proband_id"].startswith("NIH"):
                ids.append(i["proband_id"])
        maximum = max(ids)
        maximum = maximum.split(" ")
        num = maximum[2].split('-')
        num[0] = str(int(num[0]) + 1)
        maximum[2] = '-'.join(num)
        maximum = " ".join(maximum)

        return maximum

    ## checks to make sure the input record id does exist in your REDCap project.
    def check_record_id(self, id_check):
        data = {
            'token': self.token,
            'content': 'record',
            'format': 'json',
            'type': 'flat',
            ## this will have to be adjusted like above
            'fields[0]': 'proband_id',
            'rawOrLabel': 'raw',
            'rawOrLabelHeaders': 'raw',
            'exportCheckboxLabel': 'false',
            'exportSurveyFields': 'false',
            'exportDataAccessGroups': 'false',
            'returnFormat': 'json'
        }
        r = requests.post(self.url, data=data)

        output = json.loads(r.text)
        ids = []
        for i in output:
            ## change "proband_id" to wherever you store your record ids.
            ids.append(i["proband_id"])
        return (id_check in ids)

    """
    Most of the stuff after this is going to be study specific. Most of the variable names will have to be adjusted
    depending on the schema of your records and project.
    """
    # TODO: Think about how to import REDCap schema and configure the crap below automatically.
    # TODO: Going through all this manually is going to be a bitch for users.


    def create_record(self, record_id, first_name, last_name, email):

        data_input = [
            {
            "proband_id":record_id,
            "proband_name_first": first_name,
            "proband_name_last": last_name,
            "proband_email": email
            }
        ]
        data = {
            'token': self.token,
            'content': 'record',
            'format': 'json',
            'type': 'flat',
            'overwriteBehavior': 'normal',
            'data': json.dumps(data_input),
            'returnContent': 'count',
            'returnFormat': 'json',
            'record_id': record_id
        }
        r = requests.post(self.url, data=data)

        return r.text

class Record(RedCap):

    def __init__(self, record_id):
        self.record = record_id
        self.url = RedCap().url
        self.token = RedCap().token

    ## FindMyVariant specific, just ignore this
    def get_pedigree(self):

        data = {
            'token':self.token,
            'content':'file',
            'action':'export',
            'record':self.record,
            'field':'study_pedigree',
            'returnFormat':'json'
        }

        r = requests.post(self.url, data=data)
        data = r.text
        return data

    # this assumes you are using surveys to capture data from your patients
    def get_link(self, index=''):
        if index == '':
            who = 'proband'
        else:
            who = 'relatives'

        data = {
            'token':self.token,
            'content':'surveyLink',
            'format':'json',
            'instrument':who,
            'repeat_instance':index,
            'record':self.record,
            'returnFormat':'json'
        }
        r = requests.post(self.url, data=data)
        return r.text

    #used to get the return code for surveys
    def get_code(self, index=''):
        if index == '':
            who = 'proband'
        else:
            who = 'relatives'

        data = {
            'token': self.token,
            'content':'surveyReturnCode',
            'format':'json',
            'instrument':who,
            'repeat_instance':index,
            'record':self.record,
            'returnFormat':'json'
        }
        r = requests.post(self.url, data=data)
        return r.text


    # this is where is going to get messy. You customize this for your own project schema
    # a lot of these are FindMyVariant specific.
    def get_record(self):
        data = {
            'token': self.token,
            'content': 'record',
            'format': 'json',
            'type': 'flat',
            'records[0]': self.record,
            'rawOrLabel': 'raw',
            'rawOrLabelHeaders': 'raw',
            'exportCheckboxLabel': 'false',
            'exportSurveyFields': 'false',
            'exportDataAccessGroups': 'false',
            'returnFormat': 'json'
        }

        r = requests.post(self.url, data=data)
        data = json.loads(r.text)
        #return json.dumps(data)
        prob_data = data[0]
        code = self.get_code()
        link = self.get_link()
        all_data = {
            "proband":{
                "proband_id":prob_data["proband_id"],
                "proband_name_first":prob_data["proband_name_first"],
                "proband_name_last":prob_data["proband_name_last"],
                "proband_address":prob_data["proband_address"],
                "proband_city":prob_data["proband_city"],
                "proband_zipcode":prob_data["proband_zipcode"],
                "proband_phone_cell":prob_data["proband_phone_cell"],
                "proband_phone_home":prob_data["proband_phone_home"],
                "proband_email":prob_data["proband_email"],
                "proband_state":prob_data["proband_state"],
                "return_code":code,
                "return_link":link
            }
        }


        num_relatives = len(data) - 1
        all_data["number_of_relatives"] = num_relatives

        kits_sent = 0
        kits_returned = 0
        vus = 0
        for i in range(1, len(data)):
            rel_data = data[i]
            rel = {
                "rel_first_name":rel_data["rel_first_name"],
                "rel_last_name":rel_data["rel_last_name"],
                "rel_address":rel_data["rel_address"],
                "rel_city":rel_data["rel_city"],
                "rel_zipcode":rel_data["rel_zipcode"],
                "rel_phone_cell":rel_data["rel_phone_cell"],
                "rel_phone_home":rel_data["rel_phone_home"],
                "rel_email":rel_data["rel_email"],
                "rel_state":rel_data["rel_state"],
                "rel_kit_sent":rel_data["rel_kit_sent"],
                "rel_relationship":rel_data["rel_relationship"],
                "rel_relationship_oth":rel_data["rel_relationship_oth"]
            }
            if rel_data['rel_kit_sent'] == '1':
                kits_sent += 1
            if rel_data['rel_kit_returned'] == '1':
                kits_returned += 1
            if rel_data['rel_vus_confirmed'] != '':
                vus += 1



            code = self.get_code(i)
            rel['return_code'] = code

            link = self.get_link(i)
            rel['return_link'] = link

            all_data[i] = rel
        all_data['add_relative_link'] = self.get_link(len(data))
        all_data["kits_returned"] = kits_returned
        all_data["kits_sent"] = kits_sent
        #all_data["pedigree"] = self.get_pedigree().encode('utf-8')
        all_data["token"] = self.token
        all_data['vus'] = vus
        return json.dumps(all_data)

