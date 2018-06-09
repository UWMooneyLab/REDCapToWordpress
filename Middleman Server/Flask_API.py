import sys
## adjust these so they are specific to your server setup
## you'll need to install python 2.7 (yeah yeah, this should be python 3.6, but I'm a lazy bum)
## and you'll need to install flask, easiest way is via pip.

sys.path.append('/data/common/FindMyVariant/lib/python2.7/site-packages/')
sys.path.append('/data/common/FindMyVariant/lib/python2.7/site-packages/flask')
from flask import Flask, request, make_response
from RedCap_API import RedCap, Record

"""
You may have to add functions and endpoints for your specific use case
Flask is fairly well documented, good luck.

Each of these endpoints is using a function from the RedCap class in RedCap_API.py.
To expand functionality, either add functions in there for consistancy, or throw your hands in the air like you just don't care
and make another class in another file. 
"""

app = Flask(__name__)
app.debug = True

# pulls all the information matching the record id submitted.
# this function makes the request through our RedCap class in RedCap_API.py
@app.route('/profile_load', methods=["POST"])
def profile():
    data = request.form

    try:
        record_id = data["record"]
    except KeyError:
        return make_response("Bad Request", 400)

    profile = Record(record_id).get_record()

    return make_response(profile, 200)


#returns the next available record id
@app.route('/next_record_id', methods=["GET"])
def next_record_id():
    redcap = RedCap()
    return make_response(redcap.generate_next_record_id(), 200)


#ckecks that the input record exists.
@app.route('/check_record', methods=["POST"])
def check_record():
    data = request.form
    print (data)
    try:
        record_id = data['record']
    except KeyError:
        return make_response("Bad Request", 400)

    redcap = RedCap()
    check = redcap.check_record_id(record_id)
    if check:
        return make_response('True', 200)
    else:
        return make_response('False', 200)


#creates new record.
# the posted JSON includes record, first name, last name, and email
# adjust as you see fit.
@app.route('/create_record', methods=["POST"])
def create_record():
    data = request.form
    try:
        record = data["record"]
    except KeyError:
        return make_response("Bad Request", 400)
    try:
        first_name = data["first name"]
    except KeyError:
        return make_response("Bad Request", 400)
    try:
        last_name = data["last name"]
    except KeyError:
        return make_response("Bad Request", 400)
    try:
        email = data["email"]
    except KeyError:
        return make_response("Bad Request", 400)
    if record == '':
        return make_response("Bad Request", 400)

    redcap = RedCap()

    return make_response(redcap.create_record(record, first_name, last_name, email), 200)


if __name__ == '__main__':
    app.run()
    ## app.run(host='0.0.0.0')
