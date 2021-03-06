/*
 * $Id: splay.h 113 2012-11-20 11:42:29Z slavik $
 */

#ifndef SQUID_SPLAY_H
#define SQUID_SPLAY_H

typedef struct _splay_node {
    void *data;
    struct _splay_node *left;
    struct _splay_node *right;
} splayNode;

typedef int SPLAYCMP(const void *a, const void *b);
typedef void SPLAYWALKEE(void *nodedata, void *state);
typedef void SPLAYFREE(void *);

extern int splayLastResult;

extern splayNode *splay_insert(void *, splayNode *, SPLAYCMP *);
extern splayNode *splay_splay(const void *, splayNode *, SPLAYCMP *);
extern void splay_destroy(splayNode *, SPLAYFREE *);
extern void splay_walk(splayNode *, SPLAYWALKEE *, void *);

#endif /* SQUID_SPLAY_H */
