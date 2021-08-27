var soundFactory = {

  init(lang = "en"){
    this.lang = lang;
  },

  getSound(directory, soundFile){

    console.log('modules/buddy_profile_wizard/assets/sounds/'+directory+'/'+this.lang+'/'+soundFile);
    return 'modules/buddy_profile_wizard/assets/sounds/'+directory+'/'+this.lang+'/'+soundFile;

  }


}


soundFactory.init(gameLanguage);
