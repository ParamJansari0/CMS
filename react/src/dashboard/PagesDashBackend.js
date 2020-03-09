import React from 'react';
import PageAdmin from './PageAdmin';

class Backend {
  constructor() {
    this.deleted = [];
    this.updates = [];
  }

  all() {
    return [
      {
        'id': 1,
        'title': 'Home',
        'segment': 'home',
        'body': [],
      },
      {
        'id': 2,
        'title': 'About',
        'segment': 'about',
        'body': [],
      },
      {
        'id': 3,
        'title': 'Products',
        'segment': 'products',
        'body': [],
      },
      {
        'id': 4,
        'title': 'Contact',
        'segment': 'contact',
        'body': [],
      },
    ].filter((page) => this.deleted.indexOf(page.id) === -1)
    .map((page) => {
      this.updates.forEach((update) => {
        if(update[0] === page.id){
          page[update[1]] = update[2];
        }
      });

      return page;
    });

  }

  delete(id) {
    this.deleted.push(id);
  }

  update(id, field, value){
    this.updates.push([id, field, value]);
  }
}


let backend = new Backend();

function App() {
  return (
    <div>
      <PageAdmin backend={backend}/>
    </div>
  )
}

export default App